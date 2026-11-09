@php
    $sectionLabel = 'Berita';
    $sectionRoute = route('berita.index');
    $dateObj = $post->published_at ?? $post->created_at;
    $longDate = $dateObj?->translatedFormat('j F Y');
    $coverUrl = $post->coverUrl(1280);
    $coverSrcset = $post->coverSrcset();
    $shareUrl = url()->current();
    $shareTitle = $post->title;

    $newsArticleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $post->title,
        'description' => $post->excerpt,
        'image' => [url($coverUrl)],
        'datePublished' => $dateObj?->toIso8601String(),
        'dateModified' => $post->updated_at?->toIso8601String(),
        'author' => [
            '@type' => 'Person',
            'name' => $post->displayAuthorName(),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => setting('site_full_name', 'Gerakan Pemuda Ansor'),
        ],
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $shareUrl,
        ],
    ];
@endphp

@extends('layouts.app', [
    'title' => $post->title,
    'description' => $post->excerpt,
    'image' => $coverUrl,
    'type' => 'article',
    'bgClass' => 'bg-white',
])

@push('head')
    <script type="application/ld+json">{!! \Illuminate\Support\Js::encode($newsArticleSchema) !!}</script>
@endpush

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 w-full">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[13px] text-gray-500 mb-4 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" class="hover:text-[#16a34a] transition">Beranda</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
            <a href="{{ $sectionRoute }}" class="hover:text-[#16a34a] transition">{{ $sectionLabel }}</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
            <span class="text-gray-700 font-medium truncate" aria-current="page">{{ $post->title }}</span>
        </nav>

        <article>
            {{-- H1 (32px/800) --}}
            <h1 class="text-[26px] sm:text-[32px] font-extrabold text-[#181e2b] leading-tight tracking-tight">
                {{ $post->title }}
            </h1>

            {{-- Meta (Author, Long Date, Views, Reading Time) --}}
            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-[13px] text-gray-500 pb-6 border-b border-gray-100">
                <div class="flex items-center gap-1.5">
                    <x-icon name="user" class="w-4 h-4 text-[#059669]" />
                    <span class="font-medium text-gray-700">{{ $post->displayAuthorName() }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-icon name="clock" class="w-4 h-4 text-[#059669]" />
                    <time datetime="{{ $dateObj?->toIso8601String() }}">{{ $longDate }}</time>
                </div>
                <div class="flex items-center gap-1.5">
                    <x-icon name="eye" class="w-4 h-4 text-[#059669]" />
                    <span>{{ number_format($post->views_count, 0, ',', '.') }} dilihat</span>
                </div>
            </div>

            {{-- 16:9 Cover (rounded-2xl) --}}
            <div class="mt-6 aspect-video w-full rounded-2xl overflow-hidden shadow-md bg-gray-100">
                <img
                    src="{{ $coverUrl }}"
                    @if($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(max-width: 896px) 100vw, 896px" @endif
                    alt="{{ $post->title }}"
                    width="1280"
                    height="720"
                    class="w-full h-full object-cover"
                >
            </div>

            {{-- Body in prose with green links --}}
            <div class="mt-8 prose prose-slate max-w-none prose-headings:text-[#181e2b] prose-headings:font-bold prose-p:my-0 prose-p:min-h-lh prose-p:text-gray-700 prose-p:leading-normal prose-a:text-[#16a34a] hover:prose-a:text-[#15803d] prose-blockquote:border-l-[#16a34a] prose-blockquote:bg-[#ecfdf5]/50 prose-blockquote:py-2 prose-blockquote:px-4 prose-blockquote:rounded-r-lg">
                {!! $post->content !!}
            </div>

            {{-- Share Buttons (WhatsApp, Facebook, X, Copy Link) --}}
            <div
                x-data="{
                    copied: false,
                    copyLink() {
                        navigator.clipboard.writeText(@js($shareUrl));
                        this.copied = true;
                        setTimeout(() => { this.copied = false; }, 2500);
                    }
                }"
                class="mt-10 pt-6 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <span class="text-[14px] font-semibold text-[#181e2b] flex items-center gap-2">
                    <x-icon name="share-2" class="w-4 h-4 text-[#16a34a]" />
                    <span>Bagikan {{ $sectionLabel }}:</span>
                </span>

                <div class="flex flex-wrap items-center gap-2.5">
                    <a
                        href="https://wa.me/?text={{ urlencode($shareTitle . ' - ' . $shareUrl) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#ecfdf5] text-[#047857] hover:bg-emerald-100 text-[13px] font-semibold transition"
                    >
                        <x-icon name="whatsapp" class="w-4 h-4" />
                        <span>WhatsApp</span>
                    </a>

                    <a
                        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-[13px] font-semibold transition"
                    >
                        <x-icon name="facebook" class="w-4 h-4" />
                        <span>Facebook</span>
                    </a>

                    <a
                        href="https://twitter.com/intent/tweet?text={{ urlencode($shareTitle) }}&url={{ urlencode($shareUrl) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 text-[13px] font-semibold transition"
                    >
                        <x-icon name="x-twitter" class="w-3.5 h-3.5" />
                        <span>Post di X</span>
                    </a>

                    <button
                        type="button"
                        @click="copyLink()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-[13px] font-semibold transition cursor-pointer"
                    >
                        <x-icon x-show="!copied" name="copy" class="w-4 h-4 text-gray-500" />
                        <x-icon x-cloak x-show="copied" name="check" class="w-4 h-4 text-[#16a34a]" />
                        <span x-text="copied ? 'Tautan Disalin!' : 'Salin Tautan'">Salin Tautan</span>
                    </button>
                </div>
            </div>
        </article>

        {{-- 3 Related Posts --}}
        @if($relatedPosts->isNotEmpty())
            <section class="mt-12 pt-10 border-t border-gray-200" aria-label="{{ $sectionLabel }} Terkait">
                <x-section-title :title="$sectionLabel . ' Terkait'" />

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @foreach($relatedPosts as $related)
                        <x-news-card :post="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
