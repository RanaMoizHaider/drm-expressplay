<x-layouts.app>

    <x-slot:title>Upload Video</x-slot:title>

    <h1 class="text-3xl font-bold mb-6">Upload Video</h1>

    <div class="mx-auto mt-8">
        @if(session('success'))
            <p class="text-green-600 mb-4">{{ session('success') }}</p>
        @endif

        <form action="{{ route('video.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <input type="file" name="video" id="video" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full" accept="video/*" required>
                @error('video')
                    <p class="text-red-500 mt-2">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Upload Video</button>
        </form>
    </div>

</x-layouts.app>
