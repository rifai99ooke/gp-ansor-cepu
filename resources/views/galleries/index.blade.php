@extends('layouts.app', [
    'title' => 'Galeri',
    'description' => 'Dokumentasi foto kegiatan, kaderisasi, dan pengabdian Gerakan Pemuda Ansor.',
    'bgClass' => 'bg-linear-to-br from-[#effaf8] to-[#eef5ff]',
])

@section('content')
    @php
        $galleryItems = $galleries->getCollection()->map(fn (\App\Models\Gallery $item) => [
            'id' => $item->id,
            'title' => $item->title,
            'description' => (string) ($item->description ?? ''),
            'thumbUrl' => $item->imageUrl(800),
            'fullUrl' => $item->imageUrl(1280),
            'srcset' => $item->imageSrcset(),
            'dateFormatted' => ($item->event_date ?? $item->created_at)?->translatedFormat('j F Y') ?? '',
        ])->values();
    @endphp

    <div
        x-data="{
            items: @js($galleryItems),
            lightboxOpen: false,
            returnFocus: null,
            activeIndex: 0,
            get filteredItems() {
                return this.items;
            },
            get currentItem() {
                return this.filteredItems[this.activeIndex] || null;
            },
            openLightbox(index, trigger) {
                this.activeIndex = index;
                this.returnFocus = trigger;
                this.lightboxOpen = true;
                this.$nextTick(() => this.$refs.lightbox.showModal());
            },
            closeLightbox() {
                this.$refs.lightbox.close();
            },
            trapLightboxFocus(event) {
                const buttons = [...this.$refs.lightbox.querySelectorAll('button:not([disabled])')]
                    .filter(button => button.getClientRects().length > 0);
                const first = buttons[0];
                const last = buttons.at(-1);
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            },
            nextSlide() {
                if (this.filteredItems.length === 0) return;
                this.activeIndex = (this.activeIndex + 1) % this.filteredItems.length;
            },
            prevSlide() {
                if (this.filteredItems.length === 0) return;
                this.activeIndex = (this.activeIndex - 1 + this.filteredItems.length) % this.filteredItems.length;
            }
        }"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 w-full"
    >
        {{-- Header Variant "centered" --}}
        <x-page-header
            variant="centered"
            title="Galeri"
            subtitle="Dokumentasi kegiatan, kaderisasi, dan pengabdian Gerakan Pemuda Ansor"
        />

        {{-- Search Input --}}
        <div class="mt-7 flex justify-center">
            <div class="w-full max-w-[380px]">
                <x-search-input
                    :action="route('galeri.index')"
                    :value="$searchQuery"
                    placeholder="Cari dokumentasi kegiatan..."
                    widthClass="w-full"
                />
            </div>
        </div>

        @if($galleries->isNotEmpty())
            {{-- Gallery Cards Grid (max 9 per page) --}}
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
                <template x-for="(item, index) in filteredItems" :key="item.id">
                    <article
                        tabindex="0"
                        role="button"
                        :aria-label="'Buka foto ' + item.title"
                        @click="openLightbox(index, $event.currentTarget)"
                        @keydown.enter.prevent="openLightbox(index, $event.currentTarget)"
                        @keydown.space.prevent="openLightbox(index, $event.currentTarget)"
                        class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col cursor-pointer transition duration-300 hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                    >
                        <div class="relative aspect-video w-full overflow-hidden bg-gray-100">
                            <img
                                :src="item.thumbUrl"
                                :srcset="item.srcset || ''"
                                sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 400px"
                                :alt="item.title"
                                width="800"
                                height="450"
                                loading="lazy"
                                class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex items-end justify-end p-3">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/90 text-[#16a34a] shadow-sm">
                                    <x-icon name="zoom-in" class="w-4 h-4" />
                                </span>
                            </div>
                        </div>

                        <div class="p-4 sm:p-5 flex flex-col flex-1">
                            <h2
                                class="text-[15px] sm:text-[16px] font-bold text-[#181e2b] leading-snug line-clamp-2 group-hover:text-[#16a34a] transition"
                                x-text="item.title"
                            ></h2>

                            <p
                                x-show="item.description"
                                class="mt-1.5 text-[13px] text-gray-600 leading-relaxed line-clamp-2 flex-1"
                                x-text="item.description"
                            ></p>

                            <div class="mt-3 pt-2 flex items-center gap-1.5 text-[12px] text-gray-500">
                                <x-icon name="clock" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                <span x-text="item.dateFormatted"></span>
                            </div>
                        </div>
                    </article>
                </template>
            </div>

            {{-- Pagination --}}
            <div class="mt-10">
                <x-pagination :paginator="$galleries" />
            </div>
        @else
            {{-- Empty State --}}
            <div class="mt-10 bg-white/90 rounded-2xl border border-gray-200 p-10 text-center max-w-lg mx-auto">
                <p class="text-[15px] font-semibold text-gray-800">Tidak ada dokumentasi galeri yang cocok.</p>
                <p class="mt-1 text-[13px] text-gray-500">
                    @if($searchQuery !== '')
                        Tidak ditemukan dokumentasi untuk kata kunci "{{ $searchQuery }}".
                    @else
                        Belum ada foto dokumentasi yang diterbitkan saat ini.
                    @endif
                </p>
                @if($searchQuery !== '')
                    <a
                        href="{{ route('galeri.index') }}"
                        class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white text-xs font-semibold transition"
                    >
                        Tampilkan Semua Galeri
                    </a>
                @endif
            </div>
        @endif

        {{-- Lightbox Modal --}}
        <dialog
            x-ref="lightbox"
            x-cloak
            @cancel.prevent="closeLightbox()"
            @close="lightboxOpen = false; returnFocus?.focus(); returnFocus = null"
            @keydown.tab="trapLightboxFocus($event)"
            @keydown.arrow-right.prevent="nextSlide()"
            @keydown.arrow-left.prevent="prevSlide()"
            @click.self="closeLightbox()"
            class="fixed inset-0 m-0 h-dvh max-h-none w-screen max-w-none border-0 bg-black/80 backdrop:bg-transparent backdrop-blur-xs p-4 sm:p-6 open:flex items-center justify-center"
            aria-modal="true"
            aria-label="Pratinjau foto galeri"
        >
            <template x-if="currentItem">
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full overflow-hidden">
                    {{-- Close Button --}}
                    <button
                        type="button"
                        autofocus
                        @click="closeLightbox()"
                        aria-label="Tutup pratinjau"
                        class="absolute top-3 right-3 z-20 inline-flex items-center justify-center w-9 h-9 rounded-full bg-black/60 hover:bg-black/80 text-white transition cursor-pointer"
                    >
                        <x-icon name="close" class="w-4 h-4" />
                    </button>

                    {{-- Prev / Next Navigation --}}
                    <button
                        type="button"
                        x-show="filteredItems.length > 1"
                        @click.stop="prevSlide()"
                        aria-label="Foto sebelumnya"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-20 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/90 hover:bg-white text-gray-800 shadow-md transition cursor-pointer"
                    >
                        <x-icon name="chevron-left" class="w-5 h-5" />
                    </button>

                    <button
                        type="button"
                        x-show="filteredItems.length > 1"
                        @click.stop="nextSlide()"
                        aria-label="Foto berikutnya"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-20 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/90 hover:bg-white text-gray-800 shadow-md transition cursor-pointer"
                    >
                        <x-icon name="chevron-right" class="w-5 h-5" />
                    </button>

                    <div class="aspect-video w-full bg-gray-900">
                        <img
                            :src="currentItem.fullUrl"
                            :alt="currentItem.title"
                            class="w-full h-full object-contain"
                        >
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="flex items-center gap-1.5 text-[12px] text-gray-500 mb-1.5">
                            <x-icon name="clock" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                            <span x-text="currentItem.dateFormatted"></span>
                        </div>
                        <h3 class="text-[18px] sm:text-[20px] font-bold text-[#181e2b]" x-text="currentItem.title"></h3>
                        <p
                            x-show="currentItem.description"
                            class="mt-2 text-[14px] text-gray-600 leading-relaxed"
                            x-text="currentItem.description"
                        ></p>
                    </div>
                </div>
            </template>
        </dialog>

        {{-- Hairline Divider Below --}}
        <div class="mt-14 h-px w-full max-w-4xl mx-auto bg-linear-to-r from-transparent via-green-500/40 to-transparent"></div>
    </div>
@endsection
