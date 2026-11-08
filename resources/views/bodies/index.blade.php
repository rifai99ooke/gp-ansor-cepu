@extends('layouts.app', [
    'title' => 'Badan & Lembaga',
    'description' => 'Badan dan Lembaga yang ada di bawah naungan Gerakan Pemuda Ansor.',
    'bgClass' => 'bg-[#f0fdf4]',
])

@section('content')
    @php
        $bodiesJson = $bodies->map(fn ($b) => [
            'id' => $b->id,
            'text' => mb_strtolower($b->name . ' ' . strip_tags((string) $b->description)),
        ])->values();
    @endphp

    <div
        x-data="{
            search: '',
            items: @js($bodiesJson),
            matches(text) {
                const q = this.search.trim().toLowerCase();
                if (!q) return true;
                return text.includes(q);
            },
            get visibleCount() {
                const q = this.search.trim().toLowerCase();
                if (!q) return this.items.length;
                return this.items.filter(i => i.text.includes(q)).length;
            }
        }"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-14 w-full"
    >
        {{-- Header Variant "uppercase" --}}
        <x-page-header
            variant="uppercase"
            title="BADAN & LEMBAGA"
            subtitle="Badan dan Lembaga yang ada di bawah naungan GP Ansor."
        />

        {{-- Centered Search (280px) --}}
        <div
            class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3"
            @if($searchQuery !== '')
                @input="if ($event.target.value.trim() === '') { window.location.assign(@js(route('badan-lembaga.index'))); }"
            @endif
        >
            <x-search-input
                :action="route('badan-lembaga.index')"
                name="q"
                :value="$searchQuery"
                :model="$searchQuery === '' ? 'search' : null"
                placeholder="Cari badan atau lembaga..."
                widthClass="w-full max-w-[280px]"
            />
            @if($searchQuery !== '')
                <a href="{{ route('badan-lembaga.index') }}" aria-label="Hapus filter pencarian" class="text-[13px] font-semibold text-[#15803d] hover:underline">
                    Hapus filter
                </a>
            @endif
        </div>

        {{-- Grid of 4 columns (max-w-3xl centered, gap 32px = gap-8, 2 columns on mobile) --}}
        @if($bodies->isNotEmpty())
            <div class="mt-10 max-w-3xl mx-auto grid grid-cols-2 sm:grid-cols-4 gap-8 justify-items-center">
                @foreach($bodies as $body)
                    @php
                        $logoUrl = $body->logoUrl(480);
                        $searchable = mb_strtolower($body->name . ' ' . strip_tags((string) $body->description));
                    @endphp
                    <a
                        href="{{ route('badan-lembaga.show', $body->slug) }}"
                        x-show="matches(@js($searchable))"
                        x-transition.opacity
                        class="group flex flex-col items-center text-center focus:outline-none"
                    >
                        {{-- 100px Circle (1px border #86efac, white/translucent fill, logo object-contain at ~60%) --}}
                        <div class="w-[100px] h-[100px] rounded-full border border-[#86efac] bg-white/90 shadow-md flex items-center justify-center transition duration-300 group-hover:-translate-y-1 group-hover:ring-4 group-hover:ring-green-400/30 group-focus-visible:ring-4 group-focus-visible:ring-green-500/40">
                            @if($logoUrl)
                                <img
                                    src="{{ $logoUrl }}"
                                    alt="Logo {{ $body->name }}"
                                    width="60"
                                    height="60"
                                    loading="lazy"
                                    class="w-[60%] h-[60%] object-contain"
                                >
                            @else
                                <span class="text-lg font-extrabold text-[#166534]">{{ $body->initials() }}</span>
                            @endif
                        </div>

                        {{-- Label Pill with Uppercase Name --}}
                        <span class="mt-3 inline-block rounded-full bg-linear-to-r from-[#dcfce7] to-[#bbf7d0] text-[#166534] text-[12px] font-bold uppercase px-4 py-1.5 shadow-md leading-tight tracking-wide">
                            {{ $body->name }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Client-Side Search Empty State --}}
            <div
                x-cloak
                x-show="visibleCount === 0"
                class="mt-10 max-w-md mx-auto bg-white rounded-2xl shadow-md border border-green-100 p-8 text-center"
            >
                <x-icon name="building-2" class="w-10 h-10 text-gray-400 mx-auto mb-3" />
                <p class="text-[16px] font-bold text-[#181e2b]">Badan atau lembaga tidak ditemukan</p>
                <p class="mt-1 text-[13px] text-gray-500">
                    Tidak ditemukan badan atau lembaga untuk kata kunci "<span x-text="search" class="font-medium text-gray-700"></span>".
                </p>
                <a
                    href="{{ route('badan-lembaga.index') }}"
                    class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white text-xs font-semibold transition cursor-pointer"
                >
                    Tampilkan Semua
                </a>
            </div>
        @else
            <div class="mt-10 max-w-md mx-auto bg-white rounded-2xl shadow-md border border-green-100 p-8 text-center">
                <x-icon name="building-2" class="w-10 h-10 text-gray-400 mx-auto mb-3" />
                <p class="text-[16px] font-bold text-[#181e2b]">{{ $searchQuery !== '' ? 'Badan atau lembaga tidak ditemukan' : 'Belum ada data badan atau lembaga' }}</p>
            </div>
        @endif

        {{-- Hairline Gradient Divider at the Bottom --}}
        <div class="mt-14 h-px max-w-3xl mx-auto bg-linear-to-r from-transparent via-green-500 to-transparent"></div>
    </div>
@endsection
