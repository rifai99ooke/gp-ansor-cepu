@php
    use App\Services\ImageService;

    $siteName = setting('site_name', 'GP Ansor');
    $siteFullName = setting('site_full_name', 'Gerakan Pemuda Ansor');
    $siteLogo = ImageService::url(setting('site_logo', 'branding/logo-ansor.svg'));
    $footerTagline = setting('site_tagline', 'Membentuk generasi muda yang berakhlak mulia, nasionalis, dan siap menghadapi tantangan masa depan.');
    $footerCopyright = setting('footer_copyright', 'GP Ansor. All rights reserved.');

    $phone1 = setting('contact_phone_1', '08111021934');
    $phone2 = setting('contact_phone_2', '0213162929');
    $email = setting('contact_email', 'sekretariat@ansor.id');

    $tiktokUrl = setting('social_tiktok_url');
    $facebookUrl = setting('social_facebook_url');
    $instagramUrl = setting('social_instagram_url');
    $youtubeUrl = setting('social_youtube_url');
@endphp

<footer class="mt-auto bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-10">
            {{-- Column 1: Logo + Brand Gradient Text + Tagline --}}
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 group">
                    @if($siteLogo)
                        <img
                            src="{{ $siteLogo }}"
                            alt="Logo {{ $siteFullName }}"
                            width="36"
                            height="36"
                            loading="lazy"
                            class="w-9 h-9 object-contain shrink-0"
                        >
                    @endif
                    <span class="text-[15px] font-semibold bg-linear-to-r from-[#15803d] to-[#febf02] bg-clip-text text-transparent">
                        {{ $siteFullName }}
                    </span>
                </a>
                <p class="mt-3.5 text-[14px] text-gray-500 leading-relaxed">
                    {{ $footerTagline }}
                </p>
            </div>

            {{-- Column 2: Tautan Cepat --}}
            <div>
                <h2 class="text-[14px] font-semibold text-[#15803d]">Tautan Cepat</h2>
                <div class="w-6 h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mt-1.5 mb-4"></div>
                <ul class="space-y-2.5">
                    <li>
                        <a href="{{ route('page.program') }}" class="inline-flex items-center gap-2.5 text-[14px] text-gray-500 hover:text-[#16a34a] transition">
                            <x-icon name="target" class="w-4 h-4 text-emerald-500 shrink-0" />
                            <span>Program</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('berita.index') }}" class="inline-flex items-center gap-2.5 text-[14px] text-gray-500 hover:text-[#16a34a] transition">
                            <x-icon name="newspaper" class="w-4 h-4 text-emerald-500 shrink-0" />
                            <span>Berita</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('hubungi-kami') }}" class="inline-flex items-center gap-2.5 text-[14px] text-gray-500 hover:text-[#16a34a] transition">
                            <x-icon name="message-circle" class="w-4 h-4 text-emerald-500 shrink-0" />
                            <span>Kontak</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 3: Sumber Daya --}}
            <div>
                <h2 class="text-[14px] font-semibold text-[#15803d]">Sumber Daya</h2>
                <div class="w-6 h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mt-1.5 mb-4"></div>
                <ul class="space-y-2.5">
                    <li>
                        <a
                            href="{{ $youtubeUrl ?: '#' }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2.5 text-[14px] text-gray-500 hover:text-[#16a34a] transition"
                        >
                            <x-icon name="video" class="w-4 h-4 text-emerald-500 shrink-0" />
                            <span>Video</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('galeri.index') }}" class="inline-flex items-center gap-2.5 text-[14px] text-gray-500 hover:text-[#16a34a] transition">
                            <x-icon name="image" class="w-4 h-4 text-emerald-500 shrink-0" />
                            <span>Galeri</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('page.dokumen') }}" class="inline-flex items-center gap-2.5 text-[14px] text-gray-500 hover:text-[#16a34a] transition">
                            <x-icon name="graduation-cap" class="w-4 h-4 text-emerald-500 shrink-0" />
                            <span>Dokumen</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 4: Hubungi Kami --}}
            <div>
                <h2 class="text-[14px] font-semibold text-[#15803d]">Hubungi Kami</h2>
                <div class="w-6 h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mt-1.5 mb-4"></div>

                {{-- Row of 4 Social Icons --}}
                <div class="flex items-center gap-4 mb-3.5">
                    @if($tiktokUrl)
                        <a
                            href="{{ $tiktokUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="TikTok {{ $siteName }}"
                            class="text-[#16a34a] hover:text-[#15803d] transition"
                        >
                            <x-icon name="tiktok" class="w-4 h-4" />
                        </a>
                    @endif
                    @if($facebookUrl)
                        <a
                            href="{{ $facebookUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Facebook {{ $siteName }}"
                            class="text-[#16a34a] hover:text-[#15803d] transition"
                        >
                            <x-icon name="facebook" class="w-4 h-4" />
                        </a>
                    @endif
                    @if($instagramUrl)
                        <a
                            href="{{ $instagramUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Instagram {{ $siteName }}"
                            class="text-[#16a34a] hover:text-[#15803d] transition"
                        >
                            <x-icon name="instagram" class="w-4 h-4" />
                        </a>
                    @endif
                    @if($youtubeUrl)
                        <a
                            href="{{ $youtubeUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="YouTube {{ $siteName }}"
                            class="text-[#16a34a] hover:text-[#15803d] transition"
                        >
                            <x-icon name="youtube" class="w-4 h-4" />
                        </a>
                    @endif
                </div>

                <ul class="space-y-2.5">
                    @if($phone1)
                        <li>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $phone1) }}" class="inline-flex items-center gap-2.5 text-[14px] text-gray-500 hover:text-[#16a34a] transition">
                                <x-icon name="phone" class="w-4 h-4 text-emerald-500 shrink-0" />
                                <span>{{ $phone1 }}</span>
                            </a>
                        </li>
                    @endif
                    @if($phone2)
                        <li>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $phone2) }}" class="inline-flex items-center gap-2.5 text-[14px] text-gray-500 hover:text-[#16a34a] transition">
                                <x-icon name="phone" class="w-4 h-4 text-emerald-500 shrink-0" />
                                <span>{{ $phone2 }}</span>
                            </a>
                        </li>
                    @endif
                    @if($email)
                        <li>
                            <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2.5 text-[14px] text-gray-500 hover:text-[#16a34a] transition">
                                <x-icon name="mail" class="w-4 h-4 text-emerald-500 shrink-0" />
                                <span>{{ $email }}</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t border-gray-100 text-center">
            <p class="text-[12px] text-gray-500">
                &copy; {{ now()->year }} {{ $footerCopyright }}
            </p>
        </div>
    </div>
</footer>
