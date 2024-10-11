<?php
namespace App\Services;

use Aws\MediaConvert\MediaConvertClient;
use Aws\Exception\AwsException;

class MediaConvertService
{
    protected MediaConvertClient $mediaConvertClient;

    public function __construct()
    {
        // Initialize AWS MediaConvert client with config values
        $this->mediaConvertClient = new MediaConvertClient([
            'region' => config('mediaconversion.aws.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('mediaconversion.aws.key'),
                'secret' => config('mediaconversion.aws.secret'),
            ],
            'endpoint' => config('mediaconversion.aws.endpoint'),
        ]);
    }

    public function createMediaConvertJob($filePath, $inputPath, $outputPath)
    {
        $jobSettings = [
            'TimecodeConfig' => [
                'Source' => 'ZEROBASED',
            ],
            'OutputGroups' => [
                [
                    'CustomName' => 'encryptedVids',
                    'Name' => 'DASH ISO',
                    'Outputs' => [
                        [
                            'ContainerSettings' => [
                                'Container' => 'MPD',
                            ],
                            'VideoDescription' => [
                                'CodecSettings' => [
                                    'Codec' => 'H_264',
                                    'H264Settings' => [
                                        'MaxBitrate' => 500000,
                                        'RateControlMode' => 'QVBR',
                                        'SceneChangeDetect' => 'TRANSITION_DETECTION',
                                    ],
                                ],
                            ],
                            'NameModifier' => '_vid',
                        ],
                        [
                            'ContainerSettings' => [
                                'Container' => 'MPD',
                            ],
                            'AudioDescriptions' => [
                                [
                                    'CodecSettings' => [
                                        'Codec' => 'AAC',
                                        'AacSettings' => [
                                            'Bitrate' => 96000,
                                            'CodingMode' => 'CODING_MODE_2_0',
                                            'SampleRate' => 48000,
                                        ],
                                    ],
                                ],
                            ],
                            'NameModifier' => '_aud',
                        ],
                    ],
                    'OutputGroupSettings' => [
                        'Type' => 'DASH_ISO_GROUP_SETTINGS',
                        'DashIsoGroupSettings' => [
                            'SegmentLength' => 30,
                            'Destination' => $outputPath,
                            'DestinationSettings' => [
                                'S3Settings' => [
                                    'AccessControl' => [
                                        'CannedAcl' => 'PUBLIC_READ',
                                    ],
                                ],
                            ],
                            'Encryption' => [
                                'SpekeKeyProvider' => [
                                    'ResourceId' => config('mediaconversion.resource_id'),
                                    'SystemIds' => [
                                        config('mediaconversion.widevine.id'),
                                        config('mediaconversion.marlin.id')
                                    ],
                                    'Url' => config('mediaconversion.aws.speke'),
                                ],
                            ],
                            'FragmentLength' => 2,
                        ],
                    ],
                ],
            ],
            'FollowSource' => 1,
            'Inputs' => [
                [
                    'AudioSelectors' => [
                        'Audio Selector 1' => [
                            'DefaultSelection' => 'DEFAULT',
                        ],
                    ],
                    'VideoSelector' => [],
                    'TimecodeSource' => 'ZEROBASED',
                    'FileInput' => $inputPath,
                ],
            ],
        ];

        try {
            $result = $this->mediaConvertClient->createJob([
                'Queue' => config('mediaconversion.aws.queue'),
                'Role' => config('mediaconversion.aws.role'),
                'UserMetadata' => [],
                'Settings' => $jobSettings,
                'BillingTagsSource' => 'JOB',
                'AccelerationSettings' => [
                    'Mode' => 'DISABLED',
                ],
                'StatusUpdateInterval' => 'SECONDS_60',
                'Priority' => 0,
            ]);
            return $result['Job']['Id'];
        } catch (AwsException $e) {
            \Log::error('Error creating MediaConvert job: ' . $e->getMessage());
            throw new \Exception('Error creating MediaConvert job: ' . $e->getMessage());
        }
    }
    public function getJobStatus($jobId)
    {
        try {
            $result = $this->mediaConvertClient->getJob(['Id' => $jobId]);
            return $result['Job']['Status'];
        } catch (AwsException $e) {
            throw new \Exception('Error fetching job status: ' . $e->getMessage());
        }
    }
}
