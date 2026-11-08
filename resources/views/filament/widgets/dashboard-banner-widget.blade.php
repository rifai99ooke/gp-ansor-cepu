@php
    use App\Services\ImageService;

    $siteName = setting('site_name', 'GP Ansor');
    $siteFullName = setting('site_full_name', 'Gerakan Pemuda Ansor');
    $siteLogo = ImageService::url(setting('site_logo', 'branding/logo-ansor.svg'));
    $userName = ucwords((string) (auth()->user()?->name ?? 'Administrator'));
@endphp

<x-filament-widgets::widget>
    <div class="gp-dashboard-wrapper">
        {{-- Hero Welcome Card (Identical to Public Card & Hero Styling) --}}
        <div class="gp-hero-card">
            <div class="gp-hero-accent"></div>
            <div class="gp-hero-body">
                <div class="gp-hero-info">
                    <div class="gp-hero-logo-box">
                        @if($siteLogo)
                            <img src="{{ $siteLogo }}" alt="Logo {{ $siteName }}" width="36" height="36" class="gp-hero-logo">
                        @else
                            <span class="gp-hero-logo-fallback">GP</span>
                        @endif
                    </div>
                    <div>
                        <span class="gp-hero-badge">Portal Administrasi Resmi</span>
                        <h2 class="gp-hero-title">Selamat Datang, {{ $userName }}</h2>
                        <p class="gp-hero-subtitle">
                            Kelola berita, struktur pengurus, badan &amp; lembaga, galeri kegiatan, serta pendaftaran kader <strong>{{ $siteFullName }}</strong>.
                        </p>
                    </div>
                </div>

                <div class="gp-hero-actions">
                    <a href="{{ $urls['create_post'] }}" class="gp-btn-primary">
                        <svg class="gp-icon-sm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        <span>Tulis Berita</span>
                    </a>
                    <a href="{{ $urls['create_gallery'] }}" class="gp-btn-soft">
                        <svg class="gp-icon-sm" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        <span>Unggah Foto</span>
                    </a>
                    <a href="{{ $urls['registration'] }}" class="gp-btn-outline">
                        <span>Formulir Pendaftaran</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- 4 Summary Cards (Identical to Public Contact / Badan Lembaga Cards) --}}
        <div class="gp-stats-grid">
            {{-- Card 1: Berita --}}
            <a href="{{ $urls['posts'] }}" class="gp-stat-card">
                <div class="gp-stat-top">
                    <div class="gp-stat-icon-box">
                        <svg class="gp-icon-md" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
                            <path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/>
                        </svg>
                    </div>
                    <span class="gp-stat-action">Kelola &rarr;</span>
                </div>
                <div class="gp-stat-content">
                    <p class="gp-stat-label">Berita Terbit</p>
                    <p class="gp-stat-value">{{ $beritaCount }}</p>
                    <p class="gp-stat-desc">
                        @if($draftCount > 0)
                            {{ $draftCount }} draf belum diterbitkan
                        @else
                            Artikel berita aktif di publik
                        @endif
                    </p>
                </div>
            </a>

            {{-- Card 2: Pengurus & Lembaga --}}
            <a href="{{ $urls['officers'] }}" class="gp-stat-card">
                <div class="gp-stat-top">
                    <div class="gp-stat-icon-box">
                        <svg class="gp-icon-md" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="16" y="16" width="6" height="6" rx="1"/><rect x="2" y="16" width="6" height="6" rx="1"/><rect x="9" y="2" width="6" height="6" rx="1"/>
                            <path d="M5 16v-3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3"/><path d="M12 12V8"/>
                        </svg>
                    </div>
                    <span class="gp-stat-action">Kelola &rarr;</span>
                </div>
                <div class="gp-stat-content">
                    <p class="gp-stat-label">Pengurus &amp; Badan</p>
                    <p class="gp-stat-value">{{ $officersCount }} <span class="gp-stat-slash">/</span> {{ $bodiesCount }}</p>
                    <p class="gp-stat-desc">{{ $officersCount }} Pengurus &middot; {{ $bodiesCount }} Badan &amp; Lembaga</p>
                </div>
            </a>

            {{-- Card 3: Galeri --}}
            <a href="{{ $urls['galleries'] }}" class="gp-stat-card">
                <div class="gp-stat-top">
                    <div class="gp-stat-icon-box">
                        <svg class="gp-icon-md" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                        </svg>
                    </div>
                    <span class="gp-stat-action">Kelola &rarr;</span>
                </div>
                <div class="gp-stat-content">
                    <p class="gp-stat-label">Galeri Kegiatan</p>
                    <p class="gp-stat-value">{{ $galleriesCount }}</p>
                    <p class="gp-stat-desc">Dokumentasi foto aktif</p>
                </div>
            </a>

            {{-- Card 4: Status Pendaftaran --}}
            <a href="{{ $urls['registration'] }}" class="gp-stat-card">
                <div class="gp-stat-top">
                    <div class="gp-stat-icon-box">
                        <svg class="gp-icon-md" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                            <path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>
                        </svg>
                    </div>
                    <span class="gp-stat-action">Atur &rarr;</span>
                </div>
                <div class="gp-stat-content">
                    <p class="gp-stat-label">Status Pendaftaran</p>
                    <div style="margin-top: 6px; margin-bottom: 4px;">
                        <span class="{{ $formOpen ? 'gp-status-open' : 'gp-status-closed' }}">
                            {{ $formStatusLabel }}
                        </span>
                    </div>
                    <p class="gp-stat-desc">{{ $formTitle }}</p>
                </div>
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
