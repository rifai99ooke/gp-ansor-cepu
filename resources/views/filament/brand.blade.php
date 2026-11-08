@php
    use App\Services\ImageService;

    $siteName = setting('site_name', 'GP Ansor');
    $siteLogo = ImageService::url(setting('site_logo', 'branding/logo-ansor.svg'));
@endphp

<div class="gp-brand">
    @if($siteLogo)
        <img
            src="{{ $siteLogo }}"
            alt="Logo {{ $siteName }}"
            width="32"
            height="32"
            class="gp-brand-logo"
        >
    @else
        <span class="gp-brand-fallback">GP</span>
    @endif
    <span class="gp-brand-name">{{ $siteName }}</span>
    <span class="gp-brand-badge">Admin</span>
</div>
