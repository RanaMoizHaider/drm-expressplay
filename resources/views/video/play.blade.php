<x-layouts.app>

    <x-slot:title>Play Video</x-slot:title>

    <x-slot:styles>
        <!-- Video.js CSS -->
        <link href="https://unpkg.com/video.js/dist/video-js.css" rel="stylesheet">
    </x-slot:styles>

    <div class="flex justify-center items-center min-h-screen">
        <div class="relative w-full md:w-2/3 lg:w-1/2">
            <!-- Video.js player -->
            <video id="video-player" class="video-js vjs-default-skin vjs-16-9" controls preload="auto" width="640" height="360" poster="{{ asset('img/placeholder.svg') }}">
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
        <script src="https://unpkg.com/videojs-contrib-dash/dist/videojs-dash.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var player = videojs('video-player', {
                    techOrder: ['dash', 'html5'],
                    html5: {
                        dash: {}
                    }
                });

                // Pass the PHP DRM configuration to JavaScript
                var drmConfig = @json($drmConfig);

                player.ready(function () {
                    var src = {
                        src: '{{ $videoUrl }}',
                        type: 'application/dash+xml'  // DASH manifest type
                    };

                    var keySystems = {};

                    // Conditionally add Widevine DRM if the license URI is available
                    if (drmConfig.widevineLicenseUri) {
                        keySystems['com.widevine.alpha'] = {
                            licenseUri: drmConfig.widevineLicenseUri
                        };
                    }

                    // Conditionally add Marlin DRM if the license URI is available
                    if (drmConfig.marlinLicenseUri) {
                        keySystems['urn:marlin:mas:1.0:'] = {
                            licenseUri: drmConfig.marlinLicenseUri
                        };
                    }

                    // Conditionally add PlayReady DRM if the license URI is available
                    if (drmConfig.playreadyLicenseUri) {
                        keySystems['com.microsoft.playready'] = {
                            licenseUri: drmConfig.playreadyLicenseUri
                        };
                    }

                    // Conditionally add FairPlay DRM if the license URI is available
                    if (drmConfig.fairplayLicenseUri) {
                        keySystems['com.apple.fps.1_0'] = {
                            licenseUri: drmConfig.fairplayLicenseUri
                        };
                    }

                    // Only add keySystems if any DRM configurations are available
                    if (Object.keys(keySystems).length > 0) {
                        src.keySystems = keySystems;
                    }

                    player.src(src);
                });
            });
        </script>
    </x-slot:scripts>

</x-layouts.app>
