@extends('layouts.app', [
    'title' => 'Struktur Organisasi',
    'description' => 'Struktur kepemimpinan pusat Gerakan Pemuda Ansor.',
    'bgClass' => 'bg-linear-to-br from-[#effaf8] to-[#eef5ff]',
])

@section('content')
    @php
        $officersJson = $officers->map(fn ($o) => [
            'id' => $o->id,
            'text' => mb_strtolower($o->name . ' ' . $o->position . ' ' . ($o->period ?? '')),
        ])->values();
    @endphp

    <div
        x-data="{
            search: '',
            items: @js($officersJson),
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
        {{-- Header Variant "centered" --}}
        <x-page-header
            variant="centered"
            title="Struktur Organisasi"
            subtitle="Struktur kepemimpinan pusat Gerakan Pemuda Ansor"
        />

        {{-- Centered Search Input (280px) --}}
        <div
            class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-3"
            @if($searchQuery !== '')
                @input="if ($event.target.value.trim() === '') { window.location.assign(@js(route('profil.struktur'))); }"
            @endif
        >
            <x-search-input
                :action="route('profil.struktur')"
                name="q"
                :value="$searchQuery"
                :model="$searchQuery === '' ? 'search' : null"
                placeholder="Cari pengurus..."
                widthClass="w-full max-w-[280px]"
            />
            @if($searchQuery !== '')
                <a href="{{ route('profil.struktur') }}" aria-label="Hapus filter pencarian" class="text-[13px] font-semibold text-[#15803d] hover:underline">
                    Hapus filter
                </a>
            @endif
        </div>

        {{-- Grid of Officer Cards (3 columns, max-w-5xl centered, gap 24px = gap-6) --}}
        @if($officers->isNotEmpty())
            <div class="mt-10 max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($officers as $officer)
                    @php
                        $searchable = mb_strtolower($officer->name . ' ' . $officer->position . ' ' . ($officer->period ?? ''));
                    @endphp
                    <div
                        x-show="matches(@js($searchable))"
                        x-transition.opacity
                    >
                        <x-officer-card :officer="$officer" class="h-full" />
                    </div>
                @endforeach
            </div>

            {{-- Client-side Search Empty State --}}
            <div
                x-cloak
                x-show="visibleCount === 0"
                class="mt-10 max-w-md mx-auto bg-white/90 rounded-2xl shadow-md border border-emerald-100 p-8 text-center"
            >
                <x-icon name="user" class="w-10 h-10 text-gray-400 mx-auto mb-3" />
                <p class="text-[16px] font-bold text-[#181e2b]">Pengurus tidak ditemukan</p>
                <p class="mt-1 text-[13px] text-gray-500">
                    Tidak ada pengurus yang cocok dengan kata kunci "<span x-text="search" class="font-medium text-gray-700"></span>".
                </p>
                <a
                    href="{{ route('profil.struktur') }}"
                    class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white text-xs font-semibold transition cursor-pointer"
                >
                    Tampilkan Semua Pengurus
                </a>
            </div>
        @else
            <div class="mt-10 max-w-md mx-auto bg-white/90 rounded-2xl shadow-md border border-emerald-100 p-8 text-center">
                <x-icon name="user" class="w-10 h-10 text-gray-400 mx-auto mb-3" />
                <p class="text-[16px] font-bold text-[#181e2b]">Pengurus tidak ditemukan</p>
                <p class="mt-1 text-[13px] text-gray-500">{{ $searchQuery !== '' ? 'Tidak ada pengurus yang cocok dengan kata kunci tersebut.' : 'Data struktur kepengurusan belum tersedia.' }}</p>
            </div>
        @endif

        {{-- Hairline Gradient Divider Under Grid --}}
        <div class="mt-14 h-px max-w-4xl mx-auto bg-linear-to-r from-transparent via-green-500 to-transparent"></div>
    </div>
@endsection
