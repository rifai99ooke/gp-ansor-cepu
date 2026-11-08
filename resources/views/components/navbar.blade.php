@php
    use App\Services\ImageService;

    $siteName = setting('site_name', 'GP Ansor');
    $siteLogo = ImageService::url(setting('site_logo', 'branding/logo-ansor.svg'));

    $isHome = request()->routeIs('home');
    $isBerita = request()->routeIs('berita.*');
    $isProfil = request()->routeIs('profil.*');
    $isBadan = request()->routeIs('badan-lembaga.*');
    $isRanting = request()->routeIs('ranting.*');
    $isGaleri = request()->routeIs('galeri.*');
    $isKontak = request()->routeIs('hubungi-kami');
    $isPendaftaran = request()->routeIs('pendaftaran.*');
@endphp

<header
    x-data="{
        mobileOpen: false,
        profilOpen: false,
        mobileProfilOpen: {{ $isProfil ? 'true' : 'false' }},
        closeTimer: null,
        openProfil() {
            if (this.closeTimer) clearTimeout(this.closeTimer);
            this.profilOpen = true;
        },
        scheduleCloseProfil() {
            this.closeTimer = setTimeout(() => { this.profilOpen = false; }, 140);
        }
    }"
    @keydown.escape.window="profilOpen = false; mobileOpen = false"
    class="sticky top-0 z-50 bg-white border-b border-[#e5e7eb] h-16"
>
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between gap-4" aria-label="Navigasi Utama">
        {{-- Left: Logo Emblem (32px) + Site Name --}}
        <a
            href="{{ route('home') }}"
            class="flex items-center gap-2.5 shrink-0 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
        >
            @if($siteLogo)
                <img
                    src="{{ $siteLogo }}"
                    alt="Logo {{ $siteName }}"
                    width="32"
                    height="32"
                    class="w-8 h-8 object-contain"
                >
            @else
                <span class="w-8 h-8 rounded-lg bg-[#16a34a] text-white font-bold text-xs flex items-center justify-center">GP</span>
            @endif
            <span class="text-[15px] font-medium text-gray-800 tracking-tight">{{ $siteName }}</span>
        </a>

        {{-- Center Navigation Items (Desktop >= 1024px) --}}
        <div class="hidden lg:flex items-center gap-1 xl:gap-1.5">
            <a
                href="{{ route('home') }}"
                @if($isHome) aria-current="page" @endif
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[14px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 {{ $isHome ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/70 hover:text-[#047857]' }}"
            >
                <x-icon name="home" class="w-4 h-4 {{ $isHome ? 'text-[#047857]' : 'text-[#059669]' }}" />
                <span>Beranda</span>
            </a>

            <a
                href="{{ route('berita.index') }}"
                @if($isBerita) aria-current="page" @endif
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[14px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 {{ $isBerita ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/70 hover:text-[#047857]' }}"
            >
                <x-icon name="newspaper" class="w-4 h-4 {{ $isBerita ? 'text-[#047857]' : 'text-[#059669]' }}" />
                <span>Berita</span>
            </a>

            {{-- Profil Dropdown --}}
            <div
                class="relative"
                @mouseenter="openProfil()"
                @mouseleave="scheduleCloseProfil()"
                @click.outside="profilOpen = false"
            >
                <button
                    type="button"
                    @click="profilOpen = !profilOpen"
                    :aria-expanded="profilOpen ? 'true' : 'false'"
                    aria-haspopup="true"
                    @if($isProfil) aria-current="page" @endif
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[14px] font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 {{ $isProfil ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/70 hover:text-[#047857]' }}"
                >
                    <x-icon name="user" class="w-4 h-4 {{ $isProfil ? 'text-[#047857]' : 'text-[#059669]' }}" />
                    <span>Profil</span>
                    <x-icon
                        name="chevron-down"
                        class="w-3.5 h-3.5 transition-transform duration-200 {{ $isProfil ? 'text-[#047857]' : 'text-gray-500' }}"
                        ::class="profilOpen ? 'rotate-180' : ''"
                    />
                </button>

                <div
                    x-cloak
                    x-show="profilOpen"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    class="absolute left-0 mt-1.5 w-[220px] rounded-xl bg-white shadow-lg border border-gray-100 py-2 z-50"
                    role="menu"
                >
                    <a
                        href="{{ route('profil.visi-misi') }}"
                        role="menuitem"
                        @if(request()->routeIs('profil.visi-misi')) aria-current="page" @endif
                        class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] transition {{ request()->routeIs('profil.visi-misi') ? 'bg-[#ecfdf5] text-[#047857] font-semibold' : 'text-gray-700 hover:bg-[#ecfdf5]' }}"
                    >
                        <x-icon name="target" class="w-4 h-4 text-emerald-500 shrink-0" />
                        <span>Visi &amp; Misi</span>
                    </a>
                    <a
                        href="{{ route('profil.struktur') }}"
                        role="menuitem"
                        @if(request()->routeIs('profil.struktur*')) aria-current="page" @endif
                        class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] transition {{ request()->routeIs('profil.struktur*') ? 'bg-[#ecfdf5] text-[#047857] font-semibold' : 'text-gray-700 hover:bg-[#ecfdf5]' }}"
                    >
                        <x-icon name="network" class="w-4 h-4 text-emerald-500 shrink-0" />
                        <span>Struktur Organisasi</span>
                    </a>
                </div>
            </div>

            <a
                href="{{ route('badan-lembaga.index') }}"
                @if($isBadan) aria-current="page" @endif
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[14px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 {{ $isBadan ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/70 hover:text-[#047857]' }}"
            >
                <x-icon name="building-2" class="w-4 h-4 {{ $isBadan ? 'text-[#047857]' : 'text-[#059669]' }}" />
                <span>Badan &amp; Lembaga</span>
            </a>

            <a
                href="{{ route('ranting.index') }}"
                @if($isRanting) aria-current="page" @endif
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[14px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 {{ $isRanting ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/70 hover:text-[#047857]' }}"
            >
                <x-icon name="users" class="w-4 h-4 {{ $isRanting ? 'text-[#047857]' : 'text-[#059669]' }}" />
                <span>Ranting</span>
            </a>

            <a
                href="{{ route('galeri.index') }}"
                @if($isGaleri) aria-current="page" @endif
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[14px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 {{ $isGaleri ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/70 hover:text-[#047857]' }}"
            >
                <x-icon name="image" class="w-4 h-4 {{ $isGaleri ? 'text-[#047857]' : 'text-[#059669]' }}" />
                <span>Galeri</span>
            </a>

            <a
                href="{{ route('hubungi-kami') }}"
                @if($isKontak) aria-current="page" @endif
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[14px] font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 {{ $isKontak ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/70 hover:text-[#047857]' }}"
            >
                <x-icon name="clipboard-list" class="w-4 h-4 {{ $isKontak ? 'text-[#047857]' : 'text-[#059669]' }}" />
                <span>Hubungi Kami</span>
            </a>
        </div>

        {{-- Right Actions (Desktop) --}}
        <div class="hidden lg:flex items-center gap-2 shrink-0">
            <a
                href="{{ route('pendaftaran.index') }}"
                @if($isPendaftaran) aria-current="page" @endif
                class="inline-flex items-center justify-center px-3.5 py-1.5 rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white text-[13px] font-semibold shadow-xs transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2"
            >
                Daftar
            </a>
        </div>

        {{-- Mobile Hamburger (< 1024px) --}}
        <div class="flex items-center gap-2 lg:hidden">
            <a
                href="{{ route('pendaftaran.index') }}"
                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white text-xs font-semibold transition"
            >
                Daftar
            </a>
            <button
                type="button"
                @click="mobileOpen = !mobileOpen"
                :aria-expanded="mobileOpen ? 'true' : 'false'"
                aria-label="Buka menu navigasi"
                class="inline-flex items-center justify-center w-11 h-11 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
            >
                <x-icon x-show="!mobileOpen" name="menu" class="w-5 h-5" />
                <x-icon x-cloak x-show="mobileOpen" name="close" class="w-5 h-5" />
            </button>
        </div>
    </nav>

    {{-- Mobile Collapsible Menu (< 1024px) --}}
    <div
        x-cloak
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="lg:hidden bg-white border-b border-gray-200 shadow-lg px-4 pt-2 pb-4 space-y-1"
    >
        <a
            href="{{ route('home') }}"
            @if($isHome) aria-current="page" @endif
            class="flex items-center gap-2.5 px-3 py-2.5 min-h-[44px] rounded-lg text-[14px] font-medium {{ $isHome ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/60' }}"
        >
            <x-icon name="home" class="w-4 h-4 text-[#059669]" />
            <span>Beranda</span>
        </a>

        <a
            href="{{ route('berita.index') }}"
            @if($isBerita) aria-current="page" @endif
            class="flex items-center gap-2.5 px-3 py-2.5 min-h-[44px] rounded-lg text-[14px] font-medium {{ $isBerita ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/60' }}"
        >
            <x-icon name="newspaper" class="w-4 h-4 text-[#059669]" />
            <span>Berita</span>
        </a>

        {{-- Inline Profil Accordion --}}
        <div>
            <button
                type="button"
                @click="mobileProfilOpen = !mobileProfilOpen"
                :aria-expanded="mobileProfilOpen ? 'true' : 'false'"
                class="w-full flex items-center justify-between px-3 py-2.5 min-h-[44px] rounded-lg text-[14px] font-semibold {{ $isProfil ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/60' }}"
            >
                <span class="flex items-center gap-2.5">
                    <x-icon name="user" class="w-4 h-4 text-[#059669]" />
                    <span>Profil</span>
                </span>
                <x-icon
                    name="chevron-down"
                    class="w-4 h-4 text-gray-500 transition-transform duration-200"
                    ::class="mobileProfilOpen ? 'rotate-180' : ''"
                />
            </button>
            <div x-cloak x-show="mobileProfilOpen" class="pl-6 pr-2 py-1 space-y-1">
                <a
                    href="{{ route('profil.visi-misi') }}"
                    class="flex items-center gap-2.5 px-3 py-2 min-h-[44px] rounded-lg text-[13px] {{ request()->routeIs('profil.visi-misi') ? 'bg-[#ecfdf5] text-[#047857] font-semibold' : 'text-gray-700 hover:bg-[#ecfdf5]' }}"
                >
                    <x-icon name="target" class="w-4 h-4 text-emerald-500" />
                    <span>Visi &amp; Misi</span>
                </a>
                <a
                    href="{{ route('profil.struktur') }}"
                    class="flex items-center gap-2.5 px-3 py-2 min-h-[44px] rounded-lg text-[13px] {{ request()->routeIs('profil.struktur*') ? 'bg-[#ecfdf5] text-[#047857] font-semibold' : 'text-gray-700 hover:bg-[#ecfdf5]' }}"
                >
                    <x-icon name="network" class="w-4 h-4 text-emerald-500" />
                    <span>Struktur Organisasi</span>
                </a>
            </div>
        </div>

        <a
            href="{{ route('badan-lembaga.index') }}"
            @if($isBadan) aria-current="page" @endif
            class="flex items-center gap-2.5 px-3 py-2.5 min-h-[44px] rounded-lg text-[14px] font-medium {{ $isBadan ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/60' }}"
        >
            <x-icon name="building-2" class="w-4 h-4 text-[#059669]" />
            <span>Badan &amp; Lembaga</span>
        </a>

        <a
            href="{{ route('ranting.index') }}"
            @if($isRanting) aria-current="page" @endif
            class="flex items-center gap-2.5 px-3 py-2.5 min-h-[44px] rounded-lg text-[14px] font-medium {{ $isRanting ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/60' }}"
        >
            <x-icon name="users" class="w-4 h-4 text-[#059669]" />
            <span>Ranting</span>
        </a>

        <a
            href="{{ route('galeri.index') }}"
            @if($isGaleri) aria-current="page" @endif
            class="flex items-center gap-2.5 px-3 py-2.5 min-h-[44px] rounded-lg text-[14px] font-medium {{ $isGaleri ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/60' }}"
        >
            <x-icon name="image" class="w-4 h-4 text-[#059669]" />
            <span>Galeri</span>
        </a>

        <a
            href="{{ route('hubungi-kami') }}"
            @if($isKontak) aria-current="page" @endif
            class="flex items-center gap-2.5 px-3 py-2.5 min-h-[44px] rounded-lg text-[14px] font-medium {{ $isKontak ? 'bg-[#ecfdf5] text-[#047857]' : 'text-gray-800 hover:bg-[#ecfdf5]/60' }}"
        >
            <x-icon name="clipboard-list" class="w-4 h-4 text-[#059669]" />
            <span>Hubungi Kami</span>
        </a>

        <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-2">
            <a
                href="{{ route('pendaftaran.index') }}"
                class="flex-1 inline-flex items-center justify-center min-h-[44px] px-4 py-2 rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white text-[14px] font-semibold"
            >
                Pendaftaran Kader
            </a>
        </div>
    </div>
</header>
