@extends('layouts.app', [
    'title' => $body->name . ' - Badan & Lembaga',
    'description' => strip_tags((string) $body->description) ?: ('Profil ' . $body->name . ' di bawah naungan Gerakan Pemuda Ansor.'),
    'image' => $body->logoUrl(480),
    'bgClass' => 'bg-[#f0fdf4]',
])

@section('content')
    @php
        $logoUrl = $body->logoUrl(800);
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 w-full">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[13px] text-gray-600 mb-6 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" class="hover:text-[#16a34a] transition">Beranda</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
            <a href="{{ route('badan-lembaga.index') }}" class="hover:text-[#16a34a] transition">Badan &amp; Lembaga</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
            <span class="text-[#181e2b] font-semibold truncate" aria-current="page">{{ $body->name }}</span>
        </nav>

        <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-10 border border-green-100">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-8">
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-full border border-[#86efac] bg-[#f0fdf4] shadow-md flex items-center justify-center shrink-0 p-5">
                    @if($logoUrl)
                        <img
                            src="{{ $logoUrl }}"
                            alt="Logo {{ $body->name }}"
                            width="96"
                            height="96"
                            class="w-full h-full object-contain"
                        >
                    @else
                        <span class="text-2xl font-extrabold text-[#166534]">{{ $body->initials() }}</span>
                    @endif
                </div>

                <div class="flex-1 text-center sm:text-left">
                    <span class="inline-block rounded-full bg-linear-to-r from-[#dcfce7] to-[#bbf7d0] text-[#166534] text-[11px] font-bold uppercase px-3.5 py-1 shadow-xs">
                        Badan &amp; Lembaga GP Ansor
                    </span>

                    <h1 class="mt-3 text-[26px] sm:text-[32px] font-extrabold text-[#181e2b] leading-tight">
                        {{ $body->name }}
                    </h1>

                    @if(filled($body->description))
                        <div class="mt-4 prose prose-slate max-w-none prose-p:text-gray-700 prose-p:leading-relaxed prose-a:text-[#16a34a]">
                            {!! $body->description !!}
                        </div>
                    @endif

                    <div class="mt-6 pt-5 border-t border-gray-100 flex flex-wrap items-center justify-center sm:justify-start gap-3">
                        <a
                            href="{{ route('badan-lembaga.index') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-[13px] font-semibold transition"
                        >
                            <x-icon name="arrow-left" class="w-4 h-4" />
                            <span>Semua Badan &amp; Lembaga</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($otherBodies->isNotEmpty())
            <section class="mt-12" aria-label="Badan dan Lembaga Lainnya">
                <h2 class="text-[18px] font-bold text-[#181e2b] text-center">Badan &amp; Lembaga Lainnya</h2>
                <div class="w-12 h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mx-auto mt-2 mb-8"></div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 justify-items-center">
                    @foreach($otherBodies as $other)
                        <a
                            href="{{ route('badan-lembaga.show', $other->slug) }}"
                            class="group flex flex-col items-center text-center"
                        >
                            <div class="w-20 h-20 rounded-full border border-[#86efac] bg-white shadow-sm flex items-center justify-center transition duration-300 group-hover:-translate-y-1 group-hover:ring-4 group-hover:ring-green-400/30">
                                @if($other->logoUrl(480))
                                    <img
                                        src="{{ $other->logoUrl(480) }}"
                                        alt="Logo {{ $other->name }}"
                                        width="48"
                                        height="48"
                                        loading="lazy"
                                        class="w-[60%] h-[60%] object-contain"
                                    >
                                @else
                                    <span class="text-sm font-bold text-[#166534]">{{ $other->initials() }}</span>
                                @endif
                            </div>
                            <span class="mt-2.5 inline-block rounded-full bg-linear-to-r from-[#dcfce7] to-[#bbf7d0] text-[#166534] text-[11px] font-bold uppercase px-3 py-1 shadow-xs">
                                {{ $other->name }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
