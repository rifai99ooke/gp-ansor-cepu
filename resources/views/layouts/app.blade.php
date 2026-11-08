@php
    use App\Services\ImageService;

    $siteName = setting('site_name', 'GP Ansor');
    $siteFullName = setting('site_full_name', 'Gerakan Pemuda Ansor');
    $siteTagline = setting('site_tagline', 'Membentuk generasi muda yang berakhlak mulia, nasionalis, dan siap menghadapi tantangan masa depan.');
    $faviconUrl = ImageService::url(setting('site_favicon', 'branding/logo-ansor.svg'));
    $faviconExt = strtolower(pathinfo((string) $faviconUrl, PATHINFO_EXTENSION));
    $faviconMime = match ($faviconExt) {
        'png' => 'image/png',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        default => 'image/svg+xml',
    };
    $logoUrl = url(ImageService::url(setting('site_logo', 'branding/logo-ansor.svg')) ?? '/storage/branding/logo-ansor.svg');

    $pageTitle = isset($title) && filled($title)
        ? "{$title} - {$siteName}"
        : "{$siteName} - {$siteFullName}";

    $metaDescription = isset($description) && filled($description)
        ? strip_tags((string) $description)
        : $siteTagline;

    $canonicalUrl = $canonical ?? url()->current();
    $ogImage = isset($image) && filled($image) ? url($image) : $logoUrl;
    $ogType = $type ?? 'website';
    $bodyBg = $bgClass ?? 'bg-white';

    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteFullName,
        'alternateName' => $siteName,
        'url' => url('/'),
        'logo' => $logoUrl,
        'description' => $siteTagline,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => setting('contact_address'),
            'addressLocality' => setting('address_locality', 'Jakarta Pusat'),
            'addressRegion' => setting('address_region', 'DKI Jakarta'),
            'postalCode' => setting('address_postal_code', '10450'),
            'addressCountry' => 'ID',
        ],
        'contactPoint' => [
            [
                '@type' => 'ContactPoint',
                'telephone' => setting('contact_phone_1'),
                'contactType' => 'customer service',
                'email' => setting('contact_email'),
            ],
        ],
        'sameAs' => array_values(array_filter([
            setting('social_tiktok_url'),
            setting('social_facebook_url'),
            setting('social_instagram_url'),
            setting('social_youtube_url'),
        ])),
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    @if($faviconUrl)
        <link rel="icon" type="{{ $faviconMime }}" href="{{ $faviconUrl }}">
    @endif

    {{-- Open Graph --}}
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ $siteFullName }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Organization JSON-LD --}}
    <script type="application/ld+json">{!! \Illuminate\Support\Js::encode($organizationSchema) !!}</script>

    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col antialiased text-[#181e2b] {{ $bodyBg }}">
    <a
        href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:bg-[#16a34a] focus:text-white focus:rounded-lg focus:shadow-lg"
    >
        Langsung ke konten utama
    </a>

    <x-navbar />

    <main id="main-content" class="flex-1 flex flex-col">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <x-footer />

    @stack('scripts')
</body>
</html>
