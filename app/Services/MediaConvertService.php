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
            'version' => '2017-08-29',
            'credentials' => [
                'key' => config('mediaconversion.aws.key'),
                'secret' => config('mediaconversion.aws.secret'),
            ],
            'endpoint' => config('mediaconversion.aws.endpoint'),
        ]);
    }

    public function createMediaConvertJob($inputPath)
    {
        \Log::info('Creating MediaConvert job for ' . $inputPath);
        \Log::info('AWS Role: ' . config('mediaconversion.aws.role'));
        $jobSettings = [
            'Inputs' => [
                [
                    'FileInput' => 's3://' . config('filesystems.disks.storj.bucket') . '/' . $inputPath,
                    'AudioSelectors' => [
                        'Audio Selector 1' => ['DefaultSelection' => 'DEFAULT'],
                    ],
                    'VideoSelector' => [
                        'ColorSpace' => 'FOLLOW',
                    ],
                ],
            ],
            'OutputGroups' => [
                [
                    'Name' => 'DASH ISO',
                    'Outputs' => [
                        [
                            'ContainerSettings' => [
                                'Container' => 'MPEG_DASH',
                            ],
                            'VideoDescription' => [
                                'CodecSettings' => [
                                    'Codec' => 'H_264',
                                ],
                            ],
                            'DRM' => [
                                'Widevine' => [
                                    'ContentId' => config('mediaconversion.widevine.id'),
                                    'KeyProviderSettings' => [
                                        'SpekeKeyProvider' => [
                                            'ResourceId' => config('mediaconversion.widevine.id'),
                                            'RoleArn' => config('mediaconversion.aws.role'),
                                            'SystemIds' => ['edef8ba9-79d6-4ace-a3c8-27dcd51d21ed'],
                                            'Url' => config('mediaconversion.aws.speke'),
                                        ],
                                    ],
                                ],
//                                'PlayReady' => [
//                                    'ContentId' => config('mediaconversion.playready.id'),
//                                    'KeyProviderSettings' => [
//                                        'SpekeKeyProvider' => [
//                                            'ResourceId' => config('mediaconversion.playready.id'),
//                                            'RoleArn' => config('mediaconversion.aws.role'),
//                                            'SystemIds' => ['9a04f079-9840-4286-ab92-e65be0885f95'],
//                                            'Url' => config('mediaconversion.aws.speke'),
//                                        ],
//                                    ],
//                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        try {
            $result = $this->mediaConvertClient->createJob([
                'Role' => config('mediaconversion.aws.role'),
                'Settings' => $jobSettings
            ]);
            return $result['Job']['Id'];
        } catch (AwsException $e) {
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
