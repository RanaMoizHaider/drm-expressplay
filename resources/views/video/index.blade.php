<x-layouts.app>

    <x-slot:title>All Videos</x-slot:title>

    <h1 class="text-3xl font-bold mb-6">Available Videos</h1>

    <!-- Check if there are videos -->
    @if(count($videos) > 0)
        <!-- List all videos -->
        <ul class="space-y-4">
            @foreach ($videos as $video)
                <li class="flex justify-between items-center p-4 bg-gray-800 rounded-lg shadow-md">
                    <span>{{ $video['name'] }}</span>
                    <a href="{{ route('video.play', ['token' => $video['token']]) }}"
                       class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600">
                        Play
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <p class="text-xl text-gray-500">No videos available.</p>
    @endif

</x-layouts.app>
