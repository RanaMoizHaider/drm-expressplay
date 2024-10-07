<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class VideoPlayer extends Component
{
    public $token;
    public $videoUrl;
    public $drmConfig;
    public $showVideo = false;

    // Mount the token to the component when it is created
    public function mount($token)
    {
        $this->token = $token;
    }

    // Function to load the video
    public function loadVideo()
    {
        // Retrieve the filename using the session-based token
        $filename = session()->get($this->token);

        if (!$filename) {
            abort(403, 'Unauthorized access');
        }

        // Generate the video stream URL securely using the token
        $this->videoUrl = route('video.stream', ['token' => $this->token]);

        // DRM configuration for Shaka Player
        $this->drmConfig = [
            'servers' => [
                'com.widevine.alpha' => config('mediaconversion.aws.speke'),
                'com.apple.fps.1_0' => config('mediaconversion.aws.speke'),
            ]
        ];

        // Emit the data to the frontend to initialize the Shaka Player
        $this->emit('videoLoaded', $this->videoUrl, $this->drmConfig);

        // Set showVideo to true to reveal the video player
        $this->showVideo = true;
    }

    public function render()
    {
        return view('livewire.video-player');
    }
}
