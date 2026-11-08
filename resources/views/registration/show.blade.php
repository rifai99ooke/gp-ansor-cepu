@php
    use App\Enums\RegistrationStatus;

    $status = $form ? $form->status() : RegistrationStatus::Closed;
    $isOpen = $form ? $form->isOpen() : false;
    $pageTitle = $form?->title ?? 'Pendaftaran Kader GP Ansor';
    $fields = $schema['fields'] ?? [];
@endphp

@extends('layouts.app', [
    'title' => $pageTitle,
    'description' => strip_tags((string) ($form?->description ?? '')) ?: 'Formulir pendaftaran daring Gerakan Pemuda Ansor.',
    'image' => $form?->coverUrl(800),
    'bgClass' => 'bg-linear-to-br from-[#effaf8] to-[#eef5ff]',
])

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 w-full">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-[13px] text-gray-600 mb-6 overflow-x-auto whitespace-nowrap">
            <a href="{{ route('home') }}" class="hover:text-[#16a34a] transition">Beranda</a>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
            <span class="text-[#181e2b] font-semibold truncate" aria-current="page">Pendaftaran</span>
        </nav>

        {{-- Header Section --}}
        <div class="text-center max-w-2xl mx-auto">
            <div class="inline-flex items-center gap-2 mb-3">
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-[12px] font-bold uppercase tracking-wide {{ $status->badgeClasses() }}">
                    {{ $status->label() }}
                </span>
                @if($form && ($form->opens_at || $form->closes_at))
                    <span class="text-[12px] text-gray-500">
                        @if($form->opens_at && $form->closes_at)
                            ({{ $form->opens_at->translatedFormat('j F Y') }} &ndash; {{ $form->closes_at->translatedFormat('j F Y') }})
                        @elseif($form->closes_at)
                            (Batas akhir: {{ $form->closes_at->translatedFormat('j F Y') }})
                        @elseif($form->opens_at)
                            (Dibuka: {{ $form->opens_at->translatedFormat('j F Y') }})
                        @endif
                    </span>
                @endif
            </div>

            <h1 class="text-[30px] sm:text-[38px] font-extrabold text-[#181e2b] tracking-tight leading-tight">
                {{ $pageTitle }}
            </h1>
            <div class="w-20 h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mx-auto mt-3"></div>

            @if($form && filled($form->description))
                <div class="mt-4 prose prose-slate max-w-2xl mx-auto text-[15px] text-gray-700 leading-relaxed">
                    {!! $form->description !!}
                </div>
            @endif
        </div>

        {{-- Native Registration Form Card or Closed/Upcoming Notice --}}
        <div class="mt-8 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            {{-- Top Accent Bar --}}
            <div class="h-1.5 w-full bg-linear-to-r from-[#16a34a] via-emerald-500 to-cyan-500"></div>

            @if($isOpen)
                @if(session('registration_submitted'))
                    <div class="p-8 sm:p-12 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-linear-to-br from-[#d1fae5] to-[#a7f3d0] text-[#16a34a] flex items-center justify-center mx-auto mb-5 shadow-xs">
                            <x-icon name="check" class="w-8 h-8" strokeWidth="2.5" />
                        </div>
                        <h2 class="text-[22px] font-bold text-[#181e2b]">Pendaftaran Berhasil Dikirim!</h2>
                        <p class="mt-2 text-[14px] text-gray-600 max-w-md mx-auto leading-relaxed">
                            Terima kasih, data pendaftaran Anda untuk <strong>{{ $pageTitle }}</strong> telah berhasil kami terima.
                        </p>
                        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                            <a
                                href="{{ route('pendaftaran.index') }}"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#16a34a] hover:bg-[#15803d] text-white text-[14px] font-semibold transition shadow-xs"
                            >
                                <span>Isi Formulir Lagi</span>
                            </a>
                            <a
                                href="{{ route('home') }}"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-[14px] font-semibold transition"
                            >
                                <span>Kembali ke Beranda</span>
                            </a>
                        </div>
                    </div>
                @elseif($schema === null)
                    <div class="p-8 sm:p-12 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4">
                            <x-icon name="clipboard-list" class="w-7 h-7" />
                        </div>
                        <h2 class="text-[20px] font-bold text-[#181e2b]">Formulir Belum Dapat Dimuat</h2>
                        <p role="alert" class="mt-2 text-[14px] text-gray-600 max-w-lg mx-auto leading-relaxed">
                            {{ $errors->first('form') ?: 'Formulir pendaftaran belum dapat dimuat. Silakan coba lagi atau isi melalui Google Form.' }}
                        </p>
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                            @if(\App\Services\GoogleFormUrlNormalizer::isAllowedFormUrl($form?->form_url))
                                <a
                                    href="{{ $form->form_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#16a34a] hover:bg-[#15803d] text-white text-[14px] font-semibold transition shadow-xs"
                                >
                                    <span>Buka Google Form</span>
                                    <x-icon name="external-link" class="w-4 h-4" />
                                </a>
                            @endif
                            <a
                                href="{{ route('pendaftaran.index') }}"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-[14px] font-semibold transition"
                            >
                                <span>Coba Lagi</span>
                            </a>
                        </div>
                    </div>
                @else
                    {{-- Card Subheader --}}
                    <div class="px-6 py-5 sm:px-8 bg-gray-50/70 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-xl bg-linear-to-br from-[#d1fae5] to-[#a7f3d0] text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                                <x-icon name="clipboard-list" class="w-5 h-5" />
                            </div>
                            <div>
                                <h2 class="text-[16px] font-bold text-[#181e2b] leading-snug">Formulir Isian Pendaftaran</h2>
                                <p class="text-[12.5px] text-gray-500 mt-0.5">Silakan lengkapi data diri Anda dengan benar pada kolom di bawah ini.</p>
                            </div>
                        </div>

                        <span class="inline-flex items-center gap-1 text-[12px] font-medium text-gray-500 bg-white px-3 py-1.5 rounded-lg border border-gray-200/80 self-start sm:self-center shrink-0">
                            <span class="text-red-500 font-bold">*</span> Wajib diisi
                        </span>
                    </div>

                    {{-- Form Body --}}
                    <form action="{{ route('pendaftaran.submit') }}" method="POST" class="p-6 sm:p-8" novalidate>
                        @csrf

                        @error('form')
                            <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-[13.5px] font-medium text-red-700">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="flex flex-col gap-6">
                            @foreach($fields as $field)
                                @php
                                    $inputName = $field['input_name'];
                                    $inputId = 'field_' . $field['id'];
                                    $isRequired = (bool) $field['required'];
                                    $fieldType = $field['type'];
                                    $options = $field['options'] ?? [];
                                    $oldValue = old($inputName);
                                    $oldTextValue = is_scalar($oldValue) ? (string) $oldValue : '';
                                    $fieldError = $errors->first($inputName) ?: $errors->first($inputName . '.*');
                                @endphp

                                <div class="flex flex-col">
                                    <label for="{{ $inputId }}" class="text-[14px] font-bold text-[#181e2b] mb-2 flex items-center gap-1">
                                        <span>{{ $field['label'] }}</span>
                                        @if($isRequired)
                                            <span class="text-red-500 font-bold" title="Wajib diisi" aria-hidden="true">*</span>
                                        @endif
                                    </label>

                                    @if(filled($field['description']))
                                        <p class="text-[12.5px] text-gray-500 -mt-1 mb-2 leading-relaxed">{{ $field['description'] }}</p>
                                    @endif

                                    @if($fieldType === 'textarea')
                                        <textarea
                                            id="{{ $inputId }}"
                                            name="{{ $inputName }}"
                                            rows="3"
                                            placeholder="{{ $field['placeholder'] }}"
                                            @if($isRequired) required @endif
                                            class="w-full rounded-xl border @if($fieldError) border-red-400 bg-red-50/30 @else border-gray-200 bg-gray-50/50 hover:border-gray-300 focus:bg-white focus:border-[#16a34a] @endif px-4 py-3 text-[14px] text-[#181e2b] placeholder-gray-400 focus:ring-3 focus:ring-green-500/15 focus:outline-none transition"
                                        >{{ $oldTextValue }}</textarea>
                                    @elseif($fieldType === 'select')
                                        <select
                                            id="{{ $inputId }}"
                                            name="{{ $inputName }}"
                                            @if($isRequired) required @endif
                                            class="w-full rounded-xl border @if($fieldError) border-red-400 bg-red-50/30 @else border-gray-200 bg-gray-50/50 hover:border-gray-300 focus:bg-white focus:border-[#16a34a] @endif px-4 py-3 text-[14px] text-[#181e2b] focus:ring-3 focus:ring-green-500/15 focus:outline-none transition"
                                        >
                                            <option value="">-- Pilih {{ $field['label'] }} --</option>
                                            @foreach($options as $option)
                                                <option value="{{ $option }}" @selected($oldTextValue === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($fieldType === 'radio')
                                        <div class="grid grid-cols-1 {{ count($options) > 1 ? 'sm:grid-cols-2' : '' }} gap-3">
                                            @foreach($options as $idx => $option)
                                                <label class="group flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-emerald-50/40 hover:border-emerald-300 has-[:checked]:border-[#16a34a] has-[:checked]:bg-[#ecfdf5]/70 cursor-pointer transition">
                                                    <input
                                                        type="radio"
                                                        name="{{ $inputName }}"
                                                        value="{{ $option }}"
                                                        @checked($oldTextValue === $option)
                                                        @if($isRequired && $idx === 0) required @endif
                                                        class="w-4 h-4 text-[#16a34a] border-gray-300 focus:ring-[#16a34a] shrink-0"
                                                    >
                                                    <span class="text-[14px] font-medium text-gray-700 group-has-[:checked]:text-[#181e2b] group-has-[:checked]:font-semibold">{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($fieldType === 'checkbox')
                                        @php
                                            $oldValues = is_array($oldValue) ? $oldValue : [];
                                        @endphp
                                        <div class="grid grid-cols-1 {{ count($options) > 1 ? 'sm:grid-cols-2' : '' }} gap-3">
                                            @foreach($options as $option)
                                                <label class="group flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 bg-gray-50/50 hover:bg-emerald-50/40 hover:border-emerald-300 has-[:checked]:border-[#16a34a] has-[:checked]:bg-[#ecfdf5]/70 cursor-pointer transition">
                                                    <input
                                                        type="checkbox"
                                                        name="{{ $inputName }}[]"
                                                        value="{{ $option }}"
                                                        @checked(in_array($option, $oldValues, true))
                                                        class="w-4 h-4 rounded text-[#16a34a] border-gray-300 focus:ring-[#16a34a] shrink-0"
                                                    >
                                                    <span class="text-[14px] font-medium text-gray-700 group-has-[:checked]:text-[#181e2b] group-has-[:checked]:font-semibold">{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <input
                                            id="{{ $inputId }}"
                                            type="{{ in_array($fieldType, ['email', 'tel'], true) ? $fieldType : 'text' }}"
                                            name="{{ $inputName }}"
                                            value="{{ $oldTextValue }}"
                                            placeholder="{{ $field['placeholder'] }}"
                                            @if($isRequired) required @endif
                                            class="w-full rounded-xl border @if($fieldError) border-red-400 bg-red-50/30 @else border-gray-200 bg-gray-50/50 hover:border-gray-300 focus:bg-white focus:border-[#16a34a] @endif px-4 py-3 text-[14px] text-[#181e2b] placeholder-gray-400 focus:ring-3 focus:ring-green-500/15 focus:outline-none transition"
                                        >
                                    @endif

                                    @if($fieldError)
                                        <p role="alert" class="mt-1.5 text-[12.5px] font-medium text-red-600">{{ $fieldError }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Form Footer Actions --}}
                        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
                            <button
                                type="reset"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 hover:text-gray-800 font-semibold text-[13.5px] transition cursor-pointer"
                            >
                                Kosongkan Formulir
                            </button>

                            <button
                                type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3 rounded-xl bg-[#16a34a] hover:bg-[#15803d] text-white font-bold text-[14px] shadow-md shadow-green-600/20 hover:shadow-lg transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                            >
                                <span>Kirim Pendaftaran</span>
                                <x-icon name="send" class="w-4 h-4" />
                            </button>
                        </div>
                    </form>
                @endif
            @else
                {{-- Notice when form is Upcoming (Segera) or Closed (Ditutup) --}}
                <div class="p-8 sm:p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl {{ $status === RegistrationStatus::Upcoming ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-500' }} flex items-center justify-center mx-auto mb-4">
                        <x-icon name="calendar" class="w-7 h-7" />
                    </div>

                    @if($status === RegistrationStatus::Upcoming)
                        <h2 class="text-[20px] font-bold text-[#181e2b]">Pendaftaran Segera Dibuka</h2>
                        <p class="mt-2 text-[14px] text-gray-600 max-w-lg mx-auto leading-relaxed">
                            Formulir pendaftaran <strong>{{ $pageTitle }}</strong> belum dibuka saat ini.
                            @if($form?->opens_at)
                                Pendaftaran akan dibuka mulai tanggal <strong>{{ $form->opens_at->translatedFormat('j F Y') }}</strong>.
                            @endif
                        </p>
                    @else
                        <h2 class="text-[20px] font-bold text-[#181e2b]">Pendaftaran Telah Ditutup</h2>
                        <p class="mt-2 text-[14px] text-gray-600 max-w-lg mx-auto leading-relaxed">
                            Masa pendaftaran untuk <strong>{{ $pageTitle }}</strong> telah berakhir
                            @if($form?->closes_at)
                                pada tanggal <strong>{{ $form->closes_at->translatedFormat('j F Y') }}</strong>.
                            @endif
                            Terima kasih atas antusiasme Anda.
                        </p>
                    @endif

                    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                        <a
                            href="{{ route('home') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#16a34a] hover:bg-[#15803d] text-white text-[13px] font-semibold transition"
                        >
                            <span>Kembali ke Beranda</span>
                        </a>
                        <a
                            href="{{ route('hubungi-kami') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-[13px] font-semibold transition"
                        >
                            <span>Hubungi Sekretariat</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
