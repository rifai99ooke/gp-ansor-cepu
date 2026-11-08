@extends('layouts.app', [
    'title' => 'Ranting',
    'description' => 'Daftar ranting Gerakan Pemuda Ansor beserta seluruh anggotanya.',
    'bgClass' => 'bg-linear-to-br from-[#effaf8] to-[#eef5ff]',
])

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-14 w-full">
        {{-- Page Header --}}
        <x-page-header
            variant="centered"
            title="Ranting"
            subtitle="Daftar ranting Gerakan Pemuda Ansor beserta jumlah anggota di setiap ranting"
        />

        {{-- Ranting Count Badge --}}
        <div class="mt-6 flex justify-center">
            <div class="inline-flex items-center gap-2 rounded-full bg-white border border-emerald-200 shadow-sm px-5 py-2">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                <span class="text-[14px] font-semibold text-[#16a34a]">
                    {{ $rantingCount }} Ranting Aktif
                </span>
            </div>
        </div>

        @if($rantings->isNotEmpty())
            {{-- Ranting Grid --}}
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($rantings as $ranting)
                    @php
                        $coverUrl = $ranting->coverUrl(800);
                    @endphp
                    <a
                        href="{{ route('ranting.show', $ranting->slug) }}"
                        class="group bg-white rounded-2xl shadow-lg shadow-slate-200/80 overflow-hidden flex flex-col transition duration-300 hover:-translate-y-1 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                    >
                        {{-- Cover Photo --}}
                        <div class="aspect-[16/9] w-full overflow-hidden bg-linear-to-br from-emerald-700 to-green-900 relative">
                            @if($coverUrl)
                                <img
                                    src="{{ $coverUrl }}"
                                    alt="Foto bersama {{ $ranting->name }}"
                                    width="800"
                                    height="450"
                                    loading="lazy"
                                    class="w-full h-full object-cover transition duration-300 group-hover:scale-105"
                                >
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-white p-6">
                                    <x-icon name="user-group" class="w-12 h-12 text-white/50" />
                                </div>
                            @endif
                        </div>

                        {{-- Card Body --}}
                        <div class="p-5 flex flex-col flex-1">
                            <h2 class="text-[16px] font-bold text-[#181e2b] group-hover:text-[#16a34a] transition leading-snug">
                                {{ $ranting->name }}
                            </h2>

                            @if(filled($ranting->description))
                                <p class="mt-1.5 text-[13px] text-gray-500 line-clamp-2">{{ $ranting->description }}</p>
                            @endif

                            <div class="mt-3 flex items-center gap-1.5">
                                <x-icon name="users" class="w-4 h-4 text-emerald-600 shrink-0" />
                                <span class="text-[12px] font-semibold text-[#16a34a]">
                                    {{ $ranting->members_count }} Anggota
                                </span>
                            </div>

                            <div class="my-3.5 border-t border-gray-100"></div>

                            <div class="mt-auto flex items-center justify-between text-[12px] text-gray-500 group-hover:text-[#16a34a] transition">
                                <span>Lihat Anggota</span>
                                <x-icon name="chevron-right" class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-0.5" />
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="mt-10 max-w-md mx-auto bg-white/90 rounded-2xl shadow-md border border-emerald-100 p-8 text-center">
                <x-icon name="user-group" class="w-10 h-10 text-gray-400 mx-auto mb-3" />
                <p class="text-[16px] font-bold text-[#181e2b]">Belum ada data ranting</p>
                <p class="mt-1 text-[13px] text-gray-500">Data ranting belum tersedia saat ini.</p>
            </div>
        @endif

        <div class="mt-14 h-px max-w-4xl mx-auto bg-linear-to-r from-transparent via-green-500 to-transparent"></div>
    </div>
@endsection
