@extends('layouts.app', [
    'title' => 'Hubungi Kami',
    'description' => 'Informasi alamat sekretariat, media sosial resmi, dan kontak langsung Gerakan Pemuda Ansor.',
    'bgClass' => 'bg-[#ecfdf5]',
])

@section('content')
    @php
        $address = setting('contact_address', 'Jl. Kramat Raya No.65 1, RT.1/RW.8, Kramat, Kec. Senen, Kota Jakarta Pusat, Daerah Khusus Ibukota Jakarta 10450');
        $mapEmbedUrl = setting('map_embed_url', 'https://maps.google.com/maps?q=GERAKAN%20PEMUDA%20ANSOR%20Jl.%20Kramat%20Raya%20No.65%20Jakarta%20Pusat&t=k&z=16&ie=UTF8&iwloc=&output=embed');

        $socialLinks = [
            [
                'name' => 'Tiktok',
                'icon' => 'tiktok',
                'handle' => setting('social_tiktok_handle'),
                'url' => setting('social_tiktok_url'),
                'action' => 'Kunjungi',
            ],
            [
                'name' => 'Facebook',
                'icon' => 'facebook',
                'handle' => setting('social_facebook_handle'),
                'url' => setting('social_facebook_url'),
                'action' => 'Kunjungi',
            ],
            [
                'name' => 'Instagram',
                'icon' => 'instagram',
                'handle' => setting('social_instagram_handle'),
                'url' => setting('social_instagram_url'),
                'action' => 'Kunjungi',
            ],
            [
                'name' => 'Youtube',
                'icon' => 'youtube',
                'handle' => setting('social_youtube_handle'),
                'url' => setting('social_youtube_url'),
                'action' => 'Kunjungi',
            ],
        ];

        $phone1 = (string) setting('contact_phone_1', '08111021934');
        $phone2 = (string) setting('contact_phone_2', '0213162929');
        $email = (string) setting('contact_email', 'sekretariat@ansor.id');

        $directContacts = [
            [
                'name' => 'Nomor Telpon 1',
                'icon' => 'smartphone',
                'value' => $phone1,
                'url' => 'tel:' . preg_replace('/[^0-9+]/', '', $phone1),
                'action' => 'Hubungi',
                'external' => false,
            ],
            [
                'name' => 'Nomor Telpon 2',
                'icon' => 'phone',
                'value' => $phone2,
                'url' => 'tel:' . preg_replace('/[^0-9+]/', '', $phone2),
                'action' => 'Hubungi',
                'external' => false,
            ],
            [
                'name' => 'Email',
                'icon' => 'mail',
                'value' => $email,
                'url' => 'mailto:' . $email,
                'action' => 'Kirim Email',
                'external' => false,
            ],
        ];
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 w-full">
        {{-- Header Variant "green-underline" --}}
        <x-page-header
            variant="green-underline"
            title="Hubungi Kami"
        />

        {{-- Alamat Card (white, rounded-xl shadow-md, p-4) --}}
        <section class="mt-8 bg-white rounded-xl shadow-md p-4 sm:p-5" aria-label="Alamat Sekretariat">
            <div class="flex items-center gap-3 mb-3.5">
                <div class="w-10 h-10 rounded-xl bg-linear-to-br from-[#d1fae5] to-[#a7f3d0] flex items-center justify-center text-emerald-600 shrink-0">
                    <x-icon name="map-pin" class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-[16px] font-bold text-[#181e2b]">Alamat</h2>
                </div>
            </div>

            @if(\App\Models\Setting::isAllowedMapEmbedUrl((string) $mapEmbedUrl))
                <div class="relative w-full h-[240px] rounded-lg overflow-hidden bg-[#d8e2dc] border border-gray-200">
                    {{-- Stylized map backdrop shown instantly while iframe loads or when offline --}}
                    <div class="absolute inset-0 pointer-events-none select-none" aria-hidden="true">
                        <svg viewBox="0 0 960 240" class="w-full h-full object-cover" preserveAspectRatio="xMidYMid slice">
                            <rect width="960" height="240" fill="#cbd5cb"/>
                            <path d="M0 40 L960 190 M0 160 L960 60 M280 0 L420 240 M640 0 L520 240 M480 0 L495 240" stroke="#e2e8f0" stroke-width="10" fill="none"/>
                            <path d="M445 0 L535 240" stroke="#fde047" stroke-width="14" fill="none"/>
                            <rect x="610" y="30" width="190" height="110" rx="14" fill="#86efac" opacity="0.45"/>
                            <rect x="120" y="130" width="160" height="80" rx="12" fill="#86efac" opacity="0.35"/>
                            <circle cx="490" cy="120" r="16" fill="#ef4444" opacity="0.25"/>
                            <path d="M490 95 C480 95 472 103 472 113 C472 126 490 142 490 142 C490 142 508 126 508 113 C508 103 500 95 490 95 Z" fill="#dc2626"/>
                            <circle cx="490" cy="113" r="5" fill="#ffffff"/>
                        </svg>
                    </div>

                    <iframe
                        src="{{ \App\Models\Setting::normalizeMapEmbedUrl((string) $mapEmbedUrl) }}"
                        title="Peta Lokasi {{ setting('site_full_name', 'Gerakan Pemuda Ansor') }}"
                        width="100%"
                        height="240"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        class="relative z-10 w-full h-[240px] border-0"
                        allowfullscreen
                    ></iframe>
                </div>
            @endif

            @if(filled($address))
                <p class="mt-2.5 text-[12px] text-gray-500">{{ $address }}</p>
            @endif
        </section>

        {{-- Below: lg:grid-cols-2 gap-8 --}}
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Left Column: Media Sosial --}}
            <section aria-label="Media Sosial">
                <div class="inline-flex items-center gap-2 pb-1.5 border-b-2 border-emerald-500">
                    <x-icon name="globe" class="w-5 h-5 text-emerald-600" />
                    <h2 class="text-[18px] font-bold text-[#181e2b]">Media Sosial</h2>
                </div>

                <div class="mt-4 space-y-3.5">
                    @php
                        $hasSocials = false;
                    @endphp
                    @foreach($socialLinks as $social)
                        @if(filled($social['handle']) || filled($social['url']))
                            @php
                                $hasSocials = true;
                            @endphp
                            <a
                                href="{{ $social['url'] ?: '#' }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="group bg-white rounded-xl shadow-md p-4 flex items-center justify-between gap-4 transition duration-200 hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                            >
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-linear-to-br from-[#d1fae5] to-[#a7f3d0] flex items-center justify-center text-emerald-600 shrink-0">
                                        <x-icon :name="$social['icon']" class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[15px] font-bold text-[#181e2b] leading-tight">{{ $social['name'] }}</p>
                                        <p class="text-[12px] text-gray-600 mt-0.5 truncate">{{ $social['handle'] ?: $social['url'] }}</p>
                                    </div>
                                </div>

                                <span class="inline-flex items-center gap-1 text-[12px] font-semibold text-emerald-600 group-hover:text-emerald-700 shrink-0">
                                    <span>{{ $social['action'] }}</span>
                                    <x-icon name="external-link" class="w-3.5 h-3.5" />
                                </span>
                            </a>
                        @endif
                    @endforeach

                    @if(! $hasSocials)
                        <div class="bg-white rounded-xl shadow-md p-6 text-center text-gray-500">
                            <p class="text-[14px]">Belum ada akun media sosial yang ditambahkan.</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Right Column: Kontak Langsung --}}
            <section aria-label="Kontak Langsung">
                <div class="inline-flex items-center gap-2 pb-1.5 border-b-2 border-emerald-500">
                    <x-icon name="phone" class="w-5 h-5 text-emerald-600" />
                    <h2 class="text-[18px] font-bold text-[#181e2b]">Kontak Langsung</h2>
                </div>

                <div class="mt-4 space-y-3.5">
                    @foreach($directContacts as $contact)
                        @if(filled($contact['value']))
                            <a
                                href="{{ $contact['url'] }}"
                                class="group bg-white rounded-xl shadow-md p-4 flex items-center justify-between gap-4 transition duration-200 hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                            >
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-linear-to-br from-[#d1fae5] to-[#a7f3d0] flex items-center justify-center text-emerald-600 shrink-0">
                                        <x-icon :name="$contact['icon']" class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[15px] font-bold text-[#181e2b] leading-tight">{{ $contact['name'] }}</p>
                                        <p class="text-[12px] text-gray-600 mt-0.5 truncate">{{ $contact['value'] }}</p>
                                    </div>
                                </div>

                                <span class="inline-flex items-center gap-1 text-[12px] font-semibold text-emerald-600 group-hover:text-emerald-700 shrink-0">
                                    <span>{{ $contact['action'] }}</span>
                                    <x-icon name="external-link" class="w-3.5 h-3.5" />
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection
