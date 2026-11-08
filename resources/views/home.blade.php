@extends('layouts.app', [
    'title' => 'Beranda',
    'description' => setting('site_tagline'),
    'bgClass' => 'bg-white',
])

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-12 w-full">
        {{-- Hero Carousel (440px desktop, 260px mobile) --}}
        <x-hero-carousel :slides="$heroSlides" heightClass="h-[260px] sm:h-[440px]" />

        {{-- Main 3-Column Layout Below Hero --}}
        <div class="mt-10 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            {{-- Left Column (col-span-2): Berita Terkini + Search + 3-col Grid (6 per page) + Pagination --}}
            <div class="lg:col-span-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <x-section-title title="Berita Terkini" />
                    <x-search-input
                        :action="route('home')"
                        :value="$searchQuery"
                        placeholder="Cari berita..."
                        widthClass="w-full sm:w-[260px]"
                    />
                </div>

                @if($latestPosts->isNotEmpty())
                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5">
                        @foreach($latestPosts as $post)
                            <x-news-card :post="$post" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        <x-pagination :paginator="$latestPosts" />
                    </div>
                @else
                    <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-10 text-center">
                        <x-icon name="newspaper" class="w-10 h-10 text-gray-400 mx-auto mb-3" />
                        <p class="text-[15px] font-semibold text-[#181e2b]">Belum ada berita</p>
                        <p class="mt-1 text-[13px] text-gray-500">
                            @if($searchQuery !== '')
                                Tidak ditemukan berita untuk kata kunci "{{ $searchQuery }}".
                            @else
                                Belum ada berita yang diterbitkan saat ini.
                            @endif
                        </p>
                        @if($searchQuery !== '')
                            <a
                                href="{{ route('home') }}"
                                class="mt-4 inline-flex items-center px-4 py-2 rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white text-xs font-semibold transition"
                            >
                                Reset Pencarian
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Right Column (col-span-1): Berita Terpopuler (6 compact rows) --}}
            <aside class="lg:col-span-1" aria-label="Berita Terpopuler">
                <x-section-title title="Berita Terpopuler" />

                @if($popularPosts->isNotEmpty())
                    <div class="mt-5 space-y-4">
                        @foreach($popularPosts as $popular)
                            @php
                                $popDate = $popular->published_at ?? $popular->created_at;
                                $popShortDate = $popDate?->translatedFormat('j M Y');
                                $popCover = $popular->coverUrl(480);
                            @endphp
                            <article class="group relative flex items-start gap-3.5">
                                <img
                                    src="{{ $popCover }}"
                                    alt="{{ $popular->title }}"
                                    width="64"
                                    height="64"
                                    loading="lazy"
                                    class="w-16 h-16 rounded-lg object-cover shrink-0 bg-gray-100"
                                >
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-[13px] font-semibold text-black leading-snug line-clamp-2 group-hover:text-[#16a34a] transition">
                                        <a href="{{ $popular->url() }}" class="focus:outline-none focus-visible:underline after:absolute after:inset-0">
                                            {{ $popular->title }}
                                        </a>
                                    </h3>
                                    <div class="mt-1.5 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-1 text-[12px] text-gray-500 min-w-0">
                                            <x-icon name="clock" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                            <time datetime="{{ $popDate?->toIso8601String() }}" class="truncate">{{ $popShortDate }}</time>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="mt-5 text-[13px] text-gray-500">Belum ada berita populer.</p>
                @endif
            </aside>
        </div>

        {{-- Ranting Section --}}
        @if($rantingCount > 0)
            <section class="mt-12" aria-label="Ranting">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                    <div>
                        <x-section-title title="Ranting" />
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#ecfdf5] border border-emerald-200 px-3 py-1 text-[12px] font-semibold text-[#047857]">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            {{ $rantingCount }} Ranting Aktif
                        </span>
                        <a
                            href="{{ route('ranting.index') }}"
                            class="text-[13px] font-semibold text-[#16a34a] hover:text-[#15803d] transition inline-flex items-center gap-1"
                        >
                            Lihat Semua
                            <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($rantings as $ranting)
                        @php $rcoverUrl = $ranting->coverUrl(800); @endphp
                        <a
                            href="{{ route('ranting.show', $ranting->slug) }}"
                            class="group bg-white rounded-2xl shadow-lg shadow-slate-200/80 overflow-hidden flex flex-col transition duration-300 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                        >
                            <div class="aspect-[16/9] w-full overflow-hidden bg-linear-to-br from-emerald-700 to-green-900">
                                @if($rcoverUrl)
                                    <img
                                        src="{{ $rcoverUrl }}"
                                        alt="Foto bersama {{ $ranting->name }}"
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
                                <p class="text-[15px] font-bold text-[#181e2b] group-hover:text-[#16a34a] transition leading-snug">
                                    {{ $ranting->name }}
                                </p>
                                @if(filled($ranting->description))
                                    <p class="mt-1 text-[12px] text-gray-500 line-clamp-1">{{ $ranting->description }}</p>
                                @endif
                                <div class="mt-2 flex items-center gap-1.5">
                                    <x-icon name="users" class="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                                    <span class="text-[12px] font-semibold text-[#16a34a]">{{ $ranting->members_count }} Anggota</span>
                                </div>
                                <div class="my-3 border-t border-gray-100"></div>
                                <div class="mt-auto flex items-center justify-between text-[12px] text-gray-500 group-hover:text-[#16a34a] transition">
                                    <span>Lihat Anggota</span>
                                    <x-icon name="chevron-right" class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-0.5" />
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Registration Banner (shown only when an active registration form exists) --}}
        @if($hasActiveRegistration)
            <section
                aria-label="Ajakan Pendaftaran Kader"
                class="mt-12 rounded-2xl bg-linear-to-r from-[#16a34a] via-[#15803d] to-[#047857] p-6 sm:p-8 shadow-lg text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6"
            >
                <div>
                    <h2 class="text-[20px] sm:text-[24px] font-bold tracking-tight">Bergabung Bersama Kami</h2>
                    <p class="mt-1 text-[14px] text-emerald-50">
                        Daftarkan diri Anda menjadi bagian dari kader Gerakan Pemuda Ansor dan Banser untuk mengabdi bagi agama dan bangsa.
                    </p>
                </div>
                <a
                    href="{{ route('pendaftaran.index') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-white text-[#15803d] hover:bg-emerald-50 font-semibold text-[14px] shadow-xs shrink-0 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                >
                    Daftar Sekarang
                </a>
            </section>
        @endif
    </div>
@endsection
