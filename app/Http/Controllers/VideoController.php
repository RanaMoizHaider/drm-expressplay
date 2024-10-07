<?php
namespace App\Http\Controllers;

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
        $files = Storage::disk('storj')->allFiles('videos');

        // Generate random tokens for each video (stored in session for demo purposes)
        $videos = [];
        foreach ($files as $file) {
            $token = Str::random(40);
            session()->put($token, $file); // Map the token to the actual file
            $videos[] = [
                'name' => basename($file),  // Extract the filename
                'token' => $token,          // The secure access token
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
        \Log::info('Function called to upload video: ' . $request->file('video')->getClientOriginalName());

        $request->validate([
            'video' => 'required|mimes:mp4|max:200000',
        ]);

        \Log::info('Uploading video: ' . $request->file('video')->getClientOriginalName());

        $file = $request->file('video');
        $filename = $file->getClientOriginalName();
        $path = Storage::disk('storj')->putFileAs('videos', $file, $filename);

        \Log::info('Uploaded video: ' . $filename);

        $accessToken = Str::random(40);
        session()->put($accessToken, $filename);

        try {
            $this->mediaConvertService->createMediaConvertJob($path);
            \Log::info('MediaConvert job created for video: ' . $filename);
        } catch (\Exception $e) {
            \Log::error('Error processing video: ' . $e->getMessage());
            return back()->withErrors('Error processing video: ' . $e->getMessage());
        }

        return redirect()->route('video.play', ['token' => $accessToken]);
    }

    public function playVideo($token)
    {
        $filename = session()->get($token);
        if (!$filename) {
            abort(403, "Unauthorized access");
        }

        return view('video.play', compact('filename', 'token'));
    }

    public function streamVideo($token)
    {
        $filename = session()->get($token);
        if (!$filename) {
            abort(403, "Unauthorized access");
        }

        return Storage::disk('storj')->response('videos/' . $filename);
    }
}
