<x-layouts.app>

    <x-slot:title>Play Video</x-slot:title>

    <div class="flex justify-center items-center min-h-screen">
        <div class="relative w-full md:w-2/3 lg:w-1/2">
            <!-- Video player -->
            <video id="video" controls autoplay class="w-full rounded-lg shadow-lg"></video>
        </div>
    </div>

    <x-slot:scripts>
        <!-- Shaka Player Initialization -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/shaka-player/3.0.10/shaka-player.compiled.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const videoUrl = "{{ $videoUrl }}";
                const drmConfig = {!! json_encode($drmConfig) !!};

                // Initialize Shaka Player
                const video = document.getElementById('video');
                const player = new shaka.Player(video);

                // Configure the DRM settings
                player.configure({ drm: drmConfig });

                // Load the video
                player.load(videoUrl).then(() => {
                    console.log('Encrypted video loaded successfully');
                }).catch((error) => {
                    console.error('Error loading video:', error);
                });
            });
        </script>
    </x-slot:scripts>

</x-layouts.app>
