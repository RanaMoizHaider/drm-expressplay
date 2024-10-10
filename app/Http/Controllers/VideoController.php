<?php
namespace App\Http\Controllers;

use App\Jobs\SubmitMediaConvertJob;
use Illuminate\Http\Request;
use App\Services\MediaConvertService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class VideoController extends Controller
{
    protected $mediaConvertService;

    public function __construct(MediaConvertService $mediaConvertService)
    {
        $this->mediaConvertService = $mediaConvertService;
    }

    // Index method to list all videos in S3
    public function index()
    {
        // Fetch folder names from the "encryptedvideos" directory in S3 bucket
        $directories = Storage::disk('s3')->directories('encryptedvideos');

        // Generate random tokens for each folder (stored in session for demo purposes)
        $videos = [];
        foreach ($directories as $directory) {
            $folderName = basename($directory); // Extract the folder name (video title)
            $token = Str::random(40);
            session()->put($token, $folderName); // Store folder name in session with a token
            $videos[] = [
                'name' => $folderName,
                'token' => $token,
            ];
        }

        return view('video.index', compact('videos'));
    }

    public function showUploadForm()
    {
        return view('video.upload');
    }

    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|mimes:mp4|max:200000',
        ]);

        $file = $request->file('video');
        $filename = $file->getClientOriginalName();
        $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $filePath = Storage::disk('s3')->putFileAs('videos', $file, $filename);

        $accessToken = Str::random(40);
        session()->put($accessToken, $filename);

        $inputPath = 's3://' . config('filesystems.disks.s3.bucket') . '/' . $filePath;
        $outputPath = 's3://' . config('filesystems.disks.s3.bucket') . '/encryptedvideos/' . $filenameWithoutExtension . '/';

        SubmitMediaConvertJob::dispatch($filePath, $inputPath, $outputPath);

        return redirect()->route('video.index');
    }

    // Generate Widevine license URL from ExpressPlay
    public function getWidevineLicenseUri($contentKey, $kid)
    {
        $response = Http::get('https://wv-gen.service.expressplay.com/hms/wv/token', [
            'customerAuthenticator' => config('mediaconversion.expressplay.api_key'),
            'kid' => $kid,
            'contentKey' => $contentKey,
            'useHttps' => true
        ]);

        if ($response->successful()) {
            return $response->body();
        } else {
            throw new \Exception('Unable to retrieve Widevine license.');
        }
    }

    // Generate Marlin license URL from ExpressPlay
    public function getMarlinLicenseUri($contentKey, $kid)
    {
        $response = Http::get('https://ms3-gen.service.expressplay.com/hms/ms3/token', [
            'customerAuthenticator' => config('mediaconversion.expressplay.api_key'),
            'kid' => $kid,
            'contentKey' => $contentKey,
            'useHttps' => true
        ]);

        if ($response->successful()) {
            return $response->body();
        } else {
            throw new \Exception('Unable to retrieve Marlin license.');
        }
    }

    public function playVideo($token)
    {
        $folderName = session()->get($token);
        if (!$folderName) {
            abort(403, "Unauthorized access");
        }

        // Fetch license URIs for Widevine and Marlin
        $widevineLicenseUri = $this->getWidevineLicenseUri(config('mediaconversion.expressplay.content_key'), config('mediaconversion.expressplay.kid'));
//        $marlinLicenseUri = $this->getMarlinLicenseUri(config('mediaconversion.expressplay.content_key'), config('mediaconversion.expressplay.kid'));

        // Generate signed URL for the .mpd manifest
//        $videoUrl = Storage::disk('s3')->temporaryUrl(
//            "encryptedvideos/{$folderName}/{$folderName}.mpd", now()->addMinutes(60)
//        );

        $videoUrl = Storage::disk('s3')->url("encryptedvideos/{$folderName}/{$folderName}.mpd");
        $videoUrl = 'https://rmhnetflix.s3.amazonaws.com' . $videoUrl;

        // DRM configuration for Video.js
        $drmConfig = [
            'widevineLicenseUri' => $widevineLicenseUri,
//            'marlinLicenseUri' => $marlinLicenseUri
        ];

        return view('video.play', compact('videoUrl', 'drmConfig'));
    }
}
