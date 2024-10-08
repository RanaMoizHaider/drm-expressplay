<?php
namespace App\Http\Controllers;

use App\Jobs\SubmitMediaConvertJob;
use Config;
use Illuminate\Http\Request;
use App\Services\MediaConvertService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    protected $mediaConvertService;

    public function __construct(MediaConvertService $mediaConvertService)
    {
        $this->mediaConvertService = $mediaConvertService;
    }

    // Index method to list all videos in Storj
    public function index()
    {
        // List all videos from the "videos" directory in Storj
//        $files = Storage::disk('storj')->allFiles('videos');
        $files = Storage::disk('s3')->allFiles('videos');

        // Generate random tokens for each video (stored in session for demo purposes)
        $videos = [];
        foreach ($files as $file) {
            $token = Str::random(40);
            session()->put($token, $file);
            $videos[] = [
                'name' => basename($file),
                'token' => $token,
            ];
        }

        return view('video.index', compact('videos'));
    }

    public function showUploadForm()
    {
//        dd(Config::get('mediaconversion'));
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
        \Log::info('Uploaded video to: ' . $filePath);

        $accessToken = Str::random(40);
        session()->put($accessToken, $filename);

        $inputPath = 's3://' . config('filesystems.disks.s3.bucket') . '/' . $filePath;
        $outputPath = 's3://' . config('filesystems.disks.s3.bucket') . '/encryptedvideos/' . $filenameWithoutExtension . '/';

        SubmitMediaConvertJob::dispatch($filePath, $inputPath, $outputPath);

//        return redirect()->route('video.play', ['token' => $accessToken]);
        return redirect()->route('video.index');
    }

    public function playVideo($token)
    {
        $filename = session()->get($token);
        if (!$filename) {
            abort(403, "Unauthorized access");
        }

        $videoUrl = route('video.stream', ['token' => $token]);

        // DRM configuration for Shaka Player
        $drmConfig = [
            'servers' => [
                'com.widevine.alpha' => config('mediaconversion.aws.speke'),
                'com.apple.fps.1_0' => config('mediaconversion.aws.speke'),
            ]
        ];

        return view('video.play', compact('videoUrl', 'drmConfig'));
    }

    public function streamVideo($token)
    {
        $filename = session()->get($token);
        if (!$filename) {
            abort(403, "Unauthorized access");
        }

        return Storage::disk('s3')->response('test' . $filename);
    }
}
