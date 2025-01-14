<x-layouts.app>

    <x-slot:title>Play Video</x-slot:title>

    <x-slot:styles>
        <!-- Video.js CSS -->
        <link href="https://unpkg.com/video.js/dist/video-js.css" rel="stylesheet" />
    </x-slot:styles>

    <div class="flex justify-center items-center">
        <div class="relative w-full md:w-2/3 lg:w-1/2">
            <!-- Video.js player -->
            <video id="video-player" class="video-js vjs-default-skin vjs-16-9" controls preload="auto" width="640" height="360">
                <!-- Placeholder for poster image -->
                <p class="vjs-no-js">
                    To view this video please enable JavaScript, and consider upgrading to a
                    web browser that
                    <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                </p>
            </video>
        </div>
    </div>

    <x-slot:scripts>
        <!-- Video.js and DASH.js -->
        <script src="https://unpkg.com/video.js/dist/video.js"></script>
        <!-- Include Dash.js library -->
        <script src="https://cdn.dashjs.org/latest/dash.all.min.js"></script>
        <!-- Include videojs-contrib-dash -->
        <script src="https://unpkg.com/videojs-contrib-dash/dist/videojs-dash.js"></script>
        <!-- Include videojs-contrib-eme -->
        <script src="https://unpkg.com/videojs-contrib-eme/dist/videojs-contrib-eme.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var player = videojs('video-player');

                // player.eme();

                // Pass the PHP DRM configuration to JavaScript
                var drmConfig = @json($drmConfig);

                var src = {
                    src: '{{ $videoUrl }}',
                    type: 'application/dash+xml'
                };

                src.keySystemOptions = [];

                if (drmConfig.widevineLicenseUri) {
                    src.keySystemOptions.push({
                        name: 'com.widevine.alpha',
                        options: {
                            serverURL: drmConfig.widevineLicenseUri
                        }
                    });
                }

                if (drmConfig.marlinLicenseUri) {
                    src.keySystemOptions.push({
                        name: 'com.microsoft.playready',
                        options: {
                            serverURL: drmConfig.marlinLicenseUri,
                            audioRobustness: 'HW_SECURE_ALL',
                            videoRobustness: 'HW_SECURE_ALL'
                        }
                    });
                }

                if (drmConfig.playreadyLicenseUri) {
                    src.keySystemOptions.push({
                        name: 'com.microsoft.playready',
                        options: {
                            serverURL: drmConfig.playreadyLicenseUri
                        }
                    });
                }

                if (drmConfig.fairplayLicenseUri) {
                    src.keySystemOptions.push({
                        name: 'com.microsoft.playready',
                        options: {
                            serverURL: drmConfig.fairplayLicenseUri
                        }
                    });
                }

                player.ready(function () {
                    player.src(src);
                });
            });
        </script>
    </x-slot:scripts>

</x-layouts.app>
