@extends('layouts.app', [
    'title' => $ranting->name,
    'description' => filled($ranting->description) ? $ranting->description : 'Halaman ranting ' . $ranting->name . ' Gerakan Pemuda Ansor',
    'image' => $ranting->coverUrl(800),
    'bgClass' => 'bg-linear-to-br from-[#effaf8] to-[#eef5ff]',
])

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 w-full">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[13px] text-gray-600 mb-6 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" class="hover:text-[#16a34a] transition">Beranda</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
            <a href="{{ route('ranting.index') }}" class="hover:text-[#16a34a] transition">Ranting</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
            <span class="text-[#181e2b] font-semibold truncate" aria-current="page">{{ $ranting->name }}</span>
        </nav>

        {{-- Main Card --}}
        <div class="bg-white rounded-2xl shadow-lg shadow-slate-200/80 overflow-hidden border border-white/80">
            {{-- Fotbar (Group Photo) --}}
            @php
                $coverUrl = $ranting->coverUrl(1280);
                $coverSrcset = $ranting->coverSrcset();
            @endphp

            @if($coverUrl)
                <div class="w-full overflow-hidden bg-linear-to-br from-emerald-700 to-green-900">
                    <img
                        src="{{ $coverUrl }}"
                        @if($coverSrcset) srcset="{{ $coverSrcset }}" sizes="100vw" @endif
                        alt="Foto bersama {{ $ranting->name }}"
                        width="1280"
                        height="720"
                        class="w-full object-cover max-h-[480px]"
                    >
                </div>
            @else
                <div class="w-full bg-linear-to-br from-emerald-700 to-green-900 flex items-center justify-center py-20">
                    <x-icon name="user-group" class="w-20 h-20 text-white/30" />
                </div>
            @endif

            {{-- Ranting Info --}}
            <div class="p-6 sm:p-8">
                <div class="inline-flex items-center gap-2 rounded-full bg-[#ecfdf5] border border-emerald-200 px-3 py-1">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span class="text-[12px] font-bold text-[#047857] uppercase tracking-wide">Ranting</span>
                </div>

                <h1 class="mt-3 text-[26px] sm:text-[32px] font-extrabold text-[#181e2b] leading-tight">
                    {{ $ranting->name }}
                </h1>

                @if(filled($ranting->description))
                    <p class="mt-2 text-[15px] text-gray-600 leading-relaxed">{{ $ranting->description }}</p>
                @endif

                <div class="mt-4 flex items-center gap-2">
                    <x-icon name="users" class="w-5 h-5 text-emerald-600 shrink-0" />
                    <span class="text-[14px] font-semibold text-[#16a34a]">
                        {{ $ranting->members->count() }} Anggota Terdaftar
                    </span>
                </div>
            </div>
        </div>

        {{-- Member List --}}
        @if($ranting->members->isNotEmpty())
            <section class="mt-10" aria-label="Daftar Anggota {{ $ranting->name }}">
                <div class="flex items-center gap-3 mb-6">
                    <x-section-title title="Daftar Anggota" />
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach($ranting->members as $member)
                        @php
                            $photoUrl = $member->photoUrl(480);
                            $photoSrcset = $member->photoSrcset();
                        @endphp
                        <div class="flex flex-col items-center text-center bg-white rounded-2xl shadow shadow-slate-200/70 p-4 border border-white/80">
                            {{-- Member Photo --}}
                            <div class="w-20 h-20 rounded-full overflow-hidden bg-linear-to-br from-emerald-700 to-green-900 shrink-0 mb-3">
                                @if($photoUrl)
                                    <img
                                        src="{{ $photoUrl }}"
                                        @if($photoSrcset) srcset="{{ $photoSrcset }}" sizes="80px" @endif
                                        alt="{{ $member->name }}"
                                        width="80"
                                        height="80"
                                        loading="lazy"
                                        class="w-full h-full object-cover object-top"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-white text-[18px] font-extrabold">
                                        {{ $member->initials() }}
                                    </div>
                                @endif
                            </div>

                            {{-- Member Name --}}
                            <p class="text-[13px] font-semibold text-[#181e2b] leading-snug line-clamp-2">
                                {{ $member->name }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </section>
        @else
            <div class="mt-10 rounded-xl border border-gray-200 bg-white/80 p-8 text-center">
                <x-icon name="users" class="w-10 h-10 text-gray-400 mx-auto mb-3" />
                <p class="text-[15px] font-semibold text-[#181e2b]">Belum ada data anggota</p>
                <p class="mt-1 text-[13px] text-gray-500">Data anggota ranting ini belum ditambahkan.</p>
            </div>
        @endif

        {{-- Back + Other Rantings --}}
        <div class="mt-8 pt-4">
            <a
                href="{{ route('ranting.index') }}"
                class="inline-flex items-center gap-2 text-[13px] font-semibold text-[#16a34a] hover:text-[#15803d] transition"
            >
                <x-icon name="arrow-left" class="w-4 h-4" />
                <span>Kembali ke Daftar Ranting</span>
            </a>
        </div>

        @if($otherRantings->isNotEmpty())
            <section class="mt-12" aria-label="Ranting Lainnya">
                <h2 class="text-[20px] font-bold text-[#181e2b] text-center">Ranting Lainnya</h2>
                <div class="w-14 h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mx-auto mt-2 mb-8"></div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($otherRantings as $other)
                        @php
                            $otherCover = $other->coverUrl(800);
                        @endphp
                        <a
                            href="{{ route('ranting.show', $other->slug) }}"
                            class="group bg-white rounded-2xl shadow-lg shadow-slate-200/80 overflow-hidden flex flex-col transition duration-300 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                        >
                            <div class="aspect-[16/9] w-full overflow-hidden bg-linear-to-br from-emerald-700 to-green-900">
                                @if($otherCover)
                                    <img
                                        src="{{ $otherCover }}"
                                        alt="Foto bersama {{ $other->name }}"
                                        width="800"
                                        height="450"
                                        loading="lazy"
                                        class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <x-icon name="user-group" class="w-10 h-10 text-white/40" />
                                    </div>
                                @endif
                            </div>
                            <div class="p-4 flex flex-col flex-1">
                                <p class="text-[14px] font-bold text-[#181e2b] group-hover:text-[#16a34a] transition line-clamp-2">
                                    {{ $other->name }}
                                </p>
                                <div class="mt-auto pt-3 flex items-center justify-between text-[12px] text-gray-500 group-hover:text-[#16a34a] transition">
                                    <span>Lihat Anggota</span>
                                    <x-icon name="chevron-right" class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-0.5" />
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
