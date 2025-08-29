<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @if ($getState())
        @php
            $videoUrl = $getState();
            $isExternal = filter_var($videoUrl, FILTER_VALIDATE_URL) && !str_contains($videoUrl, config('app.url'));
        @endphp
        
        @if ($isExternal)
            {{-- Внешнее видео (YouTube, Vimeo и т.д.) --}}
            <div class="aspect-video w-full max-w-2xl">
                @if (str_contains($videoUrl, 'youtube.com') || str_contains($videoUrl, 'youtu.be'))
                    @php
                        $videoId = '';
                        if (str_contains($videoUrl, 'youtube.com/watch')) {
                            $videoId = parse_url($videoUrl, PHP_URL_QUERY);
                            parse_str($videoId, $params);
                            $videoId = $params['v'] ?? '';
                        } elseif (str_contains($videoUrl, 'youtu.be/')) {
                            $videoId = str_replace('https://youtu.be/', '', $videoUrl);
                        }
                    @endphp
                    
                    @if ($videoId)
                        <iframe
                            src="https://www.youtube.com/embed/{{ $videoId }}"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            class="w-full h-full rounded-lg"
                        ></iframe>
                    @else
                        <div class="bg-gray-100 rounded-lg p-4 text-center">
                            <p class="text-gray-500">Некорректная ссылка на YouTube</p>
                        </div>
                    @endif
                @elseif (str_contains($videoUrl, 'vimeo.com'))
                    @php
                        $videoId = str_replace(['https://vimeo.com/', 'http://vimeo.com/'], '', $videoUrl);
                    @endphp
                    
                    <iframe
                        src="https://player.vimeo.com/video/{{ $videoId }}"
                        frameborder="0"
                        allow="autoplay; fullscreen; picture-in-picture"
                        allowfullscreen
                        class="w-full h-full rounded-lg"
                    ></iframe>
                @else
                    {{-- Другие внешние видео --}}
                    <video
                        controls
                        class="w-full h-full rounded-lg"
                        {{ $getExtraAttributes() }}
                    >
                        <source src="{{ $videoUrl }}" type="video/mp4">
                        Ваш браузер не поддерживает видео.
                    </video>
                @endif
            </div>
        @else
            {{-- Локальное видео --}}
            <div class="aspect-video w-full max-w-2xl">
                <video
                    controls
                    class="w-full h-full rounded-lg"
                    {{ $getExtraAttributes() }}
                >
                    <source src="{{ Storage::disk('public')->url($videoUrl) }}" type="video/mp4">
                    Ваш браузер не поддерживает видео.
                </video>
            </div>
        @endif
        
        <div class="mt-2 text-sm text-gray-500">
            <a href="{{ $videoUrl }}" target="_blank" class="text-primary-600 hover:text-primary-700">
                Открыть видео в новой вкладке
            </a>
        </div>
    @else
        <div class="bg-gray-100 rounded-lg p-8 text-center">
            <div class="text-gray-400 mb-2">
                <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </div>
            <p class="text-gray-500">Видео не загружено</p>
        </div>
    @endif
</x-dynamic-component>
