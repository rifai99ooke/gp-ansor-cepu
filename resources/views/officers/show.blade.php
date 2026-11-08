@extends('layouts.app', [
    'title' => $officer->name . ' - ' . $officer->position,
    'description' => strip_tags((string) $officer->bio) ?: ($officer->name . ' - ' . $officer->position . ' Gerakan Pemuda Ansor'),
    'image' => $officer->photoUrl(800),
    'bgClass' => 'bg-linear-to-br from-[#effaf8] to-[#eef5ff]',
])

@section('content')
    @php
        $photoUrl = $officer->photoUrl(800);
        $photoSrcset = $officer->photoSrcset();
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 w-full">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[13px] text-gray-600 mb-6 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" class="hover:text-[#16a34a] transition">Beranda</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
            <a href="{{ route('profil.struktur') }}" class="hover:text-[#16a34a] transition">Struktur Organisasi</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
            <span class="text-[#181e2b] font-semibold truncate" aria-current="page">{{ $officer->name }}</span>
        </nav>

        <div class="bg-white rounded-2xl shadow-lg shadow-slate-200/80 overflow-hidden border border-white/80">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-0">
                {{-- Left: Large Portrait Photo --}}
                <div class="md:col-span-5 bg-linear-to-br from-emerald-700 to-green-900">
                    @if($photoUrl)
                        <img
                            src="{{ $photoUrl }}"
                            @if($photoSrcset) srcset="{{ $photoSrcset }}" sizes="(max-width: 768px) 100vw, 400px" @endif
                            alt="{{ $officer->name }}"
                            width="800"
                            height="700"
                            class="w-full h-full object-cover object-top aspect-[8/7] md:aspect-auto"
                        >
                    @else
                        <div class="aspect-[8/7] w-full h-full flex items-center justify-center text-white p-10">
                            <div class="w-24 h-24 rounded-full bg-white/15 border border-white/30 flex items-center justify-center text-3xl font-extrabold">
                                {{ $officer->initials() }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right: Officer Details & Bio --}}
                <div class="md:col-span-7 p-6 sm:p-8 lg:p-10 flex flex-col justify-center">
                    <div class="inline-flex items-center gap-2 self-start rounded-full bg-[#ecfdf5] border border-emerald-200 px-3 py-1">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span class="text-[12px] font-bold text-[#047857] uppercase tracking-wide">{{ $officer->position }}</span>
                    </div>

                    <h1 class="mt-3 text-[26px] sm:text-[32px] font-extrabold text-[#181e2b] leading-tight">
                        {{ $officer->name }}
                    </h1>

                    @if(filled($officer->period))
                        <p class="mt-1.5 text-[13px] font-medium text-gray-500 flex items-center gap-1.5">
                            <x-icon name="calendar" class="w-4 h-4 text-emerald-600" />
                            <span>Masa Khidmat: {{ $officer->period }}</span>
                        </p>
                    @endif

                    <div class="my-5 border-t border-gray-100"></div>

                    @if(filled($officer->bio))
                        <div class="prose prose-slate max-w-none prose-p:text-gray-700 prose-p:text-[14.5px] prose-p:leading-relaxed">
                            {!! $officer->bio !!}
                        </div>
                    @else
                        <p class="text-[14px] text-gray-600 leading-relaxed">
                            Pengurus Pimpinan Pusat {{ setting('site_full_name', 'Gerakan Pemuda Ansor') }} pada jabatan {{ $officer->position }}.
                        </p>
                    @endif

                    <div class="mt-8 pt-4 border-t border-gray-100">
                        <a
                            href="{{ route('profil.struktur') }}"
                            class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#16a34a] hover:text-[#15803d] transition"
                        >
                            <x-icon name="arrow-left" class="w-4 h-4" />
                            <span>Kembali ke Struktur Organisasi</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($otherOfficers->isNotEmpty())
            <section class="mt-12" aria-label="Pengurus Lainnya">
                <h2 class="text-[20px] font-bold text-[#181e2b] text-center">Jajaran Pengurus Lainnya</h2>
                <div class="w-14 h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mx-auto mt-2 mb-8"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($otherOfficers as $other)
                        <x-officer-card :officer="$other" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
