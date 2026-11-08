@extends('layouts.app', [
    'title' => 'Berita',
    'description' => 'Berita terkini dan terpopuler seputar kegiatan Gerakan Pemuda Ansor di seluruh Indonesia.',
    'bgClass' => 'bg-white',
])

@section('content')
    {{-- Upper Container: Hero Carousel (360px) + Berita Terpopuler Carousel --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 w-full">
        <x-hero-carousel :slides="$heroSlides" heightClass="h-[260px] sm:h-[360px]" />

        {{-- Berita Terpopuler Swiper Carousel --}}
        <section class="mt-10" aria-label="Berita Terpopuler">
            <x-section-title title="Berita Terpopuler" />

            @if($popularPosts->isNotEmpty())
                <div data-popular-carousel class="mt-5 relative">
                    <div class="swiper">
                        <div class="swiper-wrapper pb-1">
                            @foreach($popularPosts as $popular)
                                @php
                                    $popDate = $popular->published_at ?? $popular->created_at;
                                    $numericDate = $popDate?->translatedFormat('j/n/Y');
                                    $popCover = $popular->coverUrl(800);
                                    $popSrcset = $popular->coverSrcset();
                                @endphp
                                <div class="swiper-slide h-auto">
                                    <article class="group relative bg-white rounded-xl border border-gray-100 shadow-xs overflow-hidden flex flex-col h-full transition duration-300 hover:shadow-md">
                                        <div class="aspect-video w-full overflow-hidden bg-gray-100">
                                            <img
                                                src="{{ $popCover }}"
                                                @if($popSrcset) srcset="{{ $popSrcset }}" sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 400px" @endif
                                                alt="{{ $popular->title }}"
                                                width="800"
                                                height="450"
                                                loading="lazy"
                                                class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
                                            >
                                        </div>
                                        <div class="p-4 flex flex-col flex-1">
                                            <h3 class="text-[15px] font-semibold text-black leading-snug line-clamp-2 group-hover:text-[#16a34a] transition">
                                                <a href="{{ $popular->url() }}" class="focus:outline-none focus-visible:underline after:absolute after:inset-0">
                                                    {{ $popular->title }}
                                                </a>
                                            </h3>
                                            <div class="mt-3 pt-1 flex items-center gap-1.5 text-[12px] text-gray-500">
                                                <x-icon name="clock" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                                <time datetime="{{ $popDate?->toIso8601String() }}">{{ $numericDate }}</time>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 36px Round White Prev/Next Buttons --}}
                    <button
                        type="button"
                        data-popular-prev
                        aria-label="Berita populer sebelumnya"
                        class="w-9 h-9 rounded-full bg-white/95 shadow-md border border-gray-100 flex items-center justify-center text-gray-700 hover:text-[#16a34a] absolute top-[42%] -translate-y-1/2 left-2 z-10 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                    >
                        <x-icon name="chevron-left" class="w-4 h-4" />
                    </button>
                    <button
                        type="button"
                        data-popular-next
                        aria-label="Berita populer berikutnya"
                        class="w-9 h-9 rounded-full bg-white/95 shadow-md border border-gray-100 flex items-center justify-center text-gray-700 hover:text-[#16a34a] absolute top-[42%] -translate-y-1/2 right-2 z-10 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                    >
                        <x-icon name="chevron-right" class="w-4 h-4" />
                    </button>

                    {{-- Carousel Dots Below (active green-600, inactive gray-300) --}}
                    <div data-popular-dots class="mt-4 flex items-center justify-center gap-1.5" role="tablist" aria-label="Indikator berita populer">
                        @foreach($popularPosts as $idx => $popular)
                            <button
                                type="button"
                                data-popular-dot="{{ $idx }}"
                                aria-label="Slide berita populer {{ $idx + 1 }}"
                                @if($idx === 0) aria-current="true" @endif
                                class="w-2 h-2 rounded-full transition {{ $idx === 0 ? 'bg-[#16a34a]' : 'bg-gray-300' }}"
                            ></button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>
    </div>

    {{-- Full-Width Band (#f9fafb, py-10): Berita Terkini --}}
    <section class="mt-10 bg-[#f9fafb] py-10 flex-1" aria-label="Berita Terkini">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <x-section-title title="Berita Terkini" />
                </div>

                <x-search-input
                    :action="route('berita.index')"
                    :value="$searchQuery"
                    placeholder="Cari berita..."
                    widthClass="w-full sm:w-[260px]"
                />
            </div>

            @if($latestPosts->isNotEmpty())
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($latestPosts as $post)
                        <x-news-card :post="$post" />
                    @endforeach
                </div>

                <div class="mt-8">
                    <x-pagination :paginator="$latestPosts" />
                </div>
            @else
                <div class="mt-6 rounded-xl border border-gray-200 bg-white p-10 text-center">
                    <x-icon name="newspaper" class="w-10 h-10 text-gray-400 mx-auto mb-3" />
                    <p class="text-[15px] font-semibold text-[#181e2b]">Belum ada berita</p>
                    <p class="mt-1 text-[13px] text-gray-500">
                        Tidak ditemukan berita yang sesuai dengan pencarian Anda.
                    </p>
                    <a
                        href="{{ route('berita.index') }}"
                        class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white text-xs font-semibold transition"
                    >
                        Tampilkan Semua Berita
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
