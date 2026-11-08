@props([
    'slides',
    'heightClass' => 'h-[260px] sm:h-[440px]',
])

@if($slides->isNotEmpty())
    <div data-hero-carousel class="w-full">
        <div class="swiper rounded-2xl shadow-xl overflow-hidden {{ $heightClass }}">
            <div class="swiper-wrapper h-full">
                @foreach($slides as $index => $slide)
                    @php
                        $dateObj = $slide->published_at ?? $slide->created_at;
                        $longDate = $dateObj?->translatedFormat('j F Y');
                        $coverUrl = $slide->coverUrl(1280);
                        $coverSrcset = $slide->coverSrcset();
                    @endphp
                    <div class="swiper-slide relative h-full w-full">
                        <a
                            href="{{ $slide->url() }}"
                            class="block relative w-full h-full group focus:outline-none"
                        >
                            <img
                                src="{{ $coverUrl }}"
                                @if($coverSrcset) srcset="{{ $coverSrcset }}" sizes="(max-width: 1280px) 100vw, 1280px" @endif
                                alt="{{ $slide->title }}"
                                width="1280"
                                height="720"
                                @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
                                class="w-full h-full object-cover transition duration-500 group-hover:scale-[1.02]"
                            >
                            <div class="absolute inset-0 bg-linear-to-t from-black/80 via-black/40 to-transparent"></div>

                            <div class="absolute inset-x-0 bottom-0 p-5 sm:p-8 md:p-10 max-w-4xl">
                                <h2 class="text-white text-[20px] sm:text-[26px] md:text-[30px] font-bold leading-tight line-clamp-2 drop-shadow-xs">
                                    {{ $slide->title }}
                                </h2>
                                @if(filled($slide->excerpt))
                                    <p class="mt-2 text-gray-200 text-[13px] sm:text-[14px] leading-relaxed line-clamp-2">
                                        {{ $slide->excerpt }}
                                    </p>
                                @endif
                                <div class="mt-2.5 sm:mt-3 flex items-center gap-1.5 text-[12px] text-gray-300">
                                    <x-icon name="clock" class="w-3.5 h-3.5 shrink-0" />
                                    <time datetime="{{ $dateObj?->toIso8601String() }}">{{ $longDate }}</time>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Centered Dots Below --}}
        @if($slides->count() > 1)
            <div data-hero-dots class="mt-3.5 flex items-center justify-center gap-1.5" role="tablist" aria-label="Pilih slide berita utama">
                @foreach($slides as $index => $slide)
                    <button
                        type="button"
                        data-slide-index="{{ $index }}"
                        aria-label="Slide {{ $index + 1 }}: {{ $slide->title }}"
                        @if($index === 0) aria-current="true" @endif
                        class="h-1.5 rounded-full transition-all duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 {{ $index === 0 ? 'w-6 bg-[#16a34a]' : 'w-1.5 bg-gray-400 hover:bg-gray-500' }}"
                    ></button>
                @endforeach
            </div>
        @endif
    </div>
@endif
