@extends('layouts.app', [
    'title' => 'Halaman Tidak Ditemukan',
    'description' => 'Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.',
    'bgClass' => 'bg-linear-to-br from-[#effaf8] to-[#eef5ff]',
])

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 text-center w-full">
        <div class="bg-white rounded-2xl shadow-lg p-8 sm:p-12 border border-emerald-100">
            <span class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#ecfdf5] text-[#16a34a] text-2xl font-extrabold mb-4">
                404
            </span>
            <h1 class="text-[28px] sm:text-[36px] font-extrabold text-[#181e2b] tracking-tight">
                Halaman Tidak Ditemukan
            </h1>
            <div class="w-16 h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mx-auto mt-3"></div>
            <p class="mt-4 text-[15px] text-gray-600 max-w-md mx-auto leading-relaxed">
                Maaf, halaman yang Anda tuju tidak tersedia, telah dipindahkan, atau alamat URL yang dimasukkan kurang tepat.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a
                    href="{{ route('home') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#16a34a] hover:bg-[#15803d] text-white text-[14px] font-semibold shadow-xs transition"
                >
                    <x-icon name="home" class="w-4 h-4" />
                    <span>Kembali ke Beranda</span>
                </a>
                <a
                    href="{{ route('berita.index') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-[14px] font-semibold transition"
                >
                    <x-icon name="newspaper" class="w-4 h-4 text-[#059669]" />
                    <span>Lihat Berita Terkini</span>
                </a>
            </div>
        </div>
    </div>
@endsection
