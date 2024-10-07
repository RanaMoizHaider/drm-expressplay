<x-layouts.app>

    <x-slot:title>Play Video</x-slot:title>

    <div class="flex justify-center items-center min-h-screen">
        <div class="relative w-full md:w-2/3 lg:w-1/2" x-data="{ showVideo: $wire.entangle('showVideo') }">
            <!-- Video player - Controlled by Livewire -->
            <div x-show="showVideo">
                <video id="video" controls autoplay class="w-full rounded-lg shadow-lg"></video>
            </div>
        </div>
    </div>

    <livewire:video-player :token="$token" />

    <x-slot:scripts>
        <!-- Shaka Player Initialization -->
        <script>
            // Listen for the 'videoLoaded' event emitted by Livewire
            Livewire.on('videoLoaded', (videoUrl, drmConfig) => {
                const video = document.getElementById('video');
                const player = new shaka.Player(video);

                player.configure({ drm: drmConfig });

                player.load(videoUrl).then(() => {
                    console.log('Video loaded successfully');
                }).catch((error) => {
                    console.error('Error loading video:', error);
                });
            });
        </script>
    </x-slot:scripts>
</x-layouts.app>
