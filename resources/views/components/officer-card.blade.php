@props([
    'officer',
])

@php
    $photoUrl = $officer->photoUrl(800);
    $photoSrcset = $officer->photoSrcset();
    $detailUrl = route('profil.struktur.show', $officer->slug);
@endphp

<a
    href="{{ $detailUrl }}"
    {{ $attributes->merge(['class' => 'group bg-white rounded-2xl shadow-lg shadow-slate-200/80 overflow-hidden flex flex-col transition duration-300 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500']) }}
>
    {{-- Portrait Photo (aspect 8/7, object-cover object-top) --}}
    <div class="aspect-[8/7] w-full overflow-hidden bg-linear-to-br from-emerald-700 to-green-900 relative">
        @if($photoUrl)
            <img
                src="{{ $photoUrl }}"
                @if($photoSrcset) srcset="{{ $photoSrcset }}" sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 340px" @endif
                alt="{{ $officer->name }}"
                width="800"
                height="700"
                loading="lazy"
                class="w-full h-full object-cover object-top transition duration-300 group-hover:scale-105"
            >
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-white p-6">
                <div class="w-20 h-20 rounded-full bg-white/15 border border-white/30 flex items-center justify-center text-2xl font-extrabold tracking-wider">
                    {{ $officer->initials() }}
                </div>
            </div>
        @endif
    </div>

    {{-- Body p-5 --}}
    <div class="p-5 flex flex-col flex-1">
        <h2 class="text-[16px] font-bold text-[#181e2b] group-hover:text-[#16a34a] transition">
            {{ $officer->name }}
        </h2>

        <div class="mt-1.5 flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0"></span>
            <span class="text-[12px] font-semibold text-[#16a34a]">{{ $officer->position }}</span>
        </div>

        <div class="my-3.5 border-t border-gray-100"></div>

        <div class="mt-auto flex items-center justify-between text-[12px] text-gray-500 group-hover:text-[#16a34a] transition">
            <span>Lihat Detail</span>
            <x-icon name="chevron-right" class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-0.5" />
        </div>
    </div>
</a>
