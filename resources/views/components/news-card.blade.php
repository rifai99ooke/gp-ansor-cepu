@props([
    'post',
    'showAuthor' => false,
    'dateFormat' => 'short', // 'short' (j M Y) | 'numeric' (j/n/Y) | 'long' (j F Y)
])

@php
    $dateObj = $post->published_at ?? $post->created_at;
    $formattedDate = match ($dateFormat) {
        'numeric' => $dateObj?->translatedFormat('j/n/Y'),
        'long' => $dateObj?->translatedFormat('j F Y'),
        default => $dateObj?->translatedFormat('j M Y'),
    };
    $postUrl = $post->url();
    $coverUrl = $post->coverUrl(800);
    $coverSrcset = $post->coverSrcset();
@endphp

<article
    {{ $attributes->merge(['class' => 'group relative bg-white rounded-xl shadow-xs border border-gray-100 overflow-hidden flex flex-col transition duration-300 hover:-translate-y-0.5 hover:shadow-md']) }}
>
    {{-- 16:9 Image Container --}}
    <div class="aspect-video w-full overflow-hidden bg-gray-100">
        <img
            src="{{ $coverUrl }}"
            @if($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 400px" @endif
            alt="{{ $post->title }}"
            width="800"
            height="450"
            loading="lazy"
            class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
        >
    </div>

    {{-- Body (16px padding = p-4) --}}
    <div class="p-4 flex flex-col flex-1">
        <h3 class="text-[15px] sm:text-[16px] font-semibold text-black leading-snug line-clamp-2 group-hover:text-[#16a34a] transition">
            <a href="{{ $postUrl }}" class="focus:outline-none focus-visible:underline after:absolute after:inset-0">
                {{ $post->title }}
            </a>
        </h3>

        @if(filled($post->excerpt))
            <p class="mt-2 text-[13px] text-gray-600 leading-relaxed line-clamp-2 flex-1">
                {{ $post->excerpt }}
            </p>
        @else
            <div class="flex-1"></div>
        @endif

        <div class="mt-3 pt-2 flex items-center justify-between gap-2 text-[12px] text-gray-500">
            <div class="flex items-center gap-1.5 min-w-0">
                <x-icon name="clock" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                <time datetime="{{ $dateObj?->toIso8601String() }}" class="truncate">{{ $formattedDate }}</time>
            </div>

            @if($showAuthor)
                <div class="flex items-center gap-1.5 text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full px-2.5 py-0.5 truncate max-w-[60%]">
                    <span class="truncate">{{ $post->displayAuthorName() }}</span>
                    <span>&middot;</span>
                    <span class="shrink-0">{{ $post->readingTimeMinutes() }} mnt</span>
                </div>
            @endif
        </div>
    </div>
</article>
