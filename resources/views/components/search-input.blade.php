@props([
    'name' => 'q',
    'value' => '',
    'placeholder' => 'Cari berita...',
    'action' => null,
    'model' => null,
    'widthClass' => 'w-full sm:w-[260px]',
])

@if($action)
    <form action="{{ $action }}" method="GET" class="{{ $widthClass }}">
        @foreach(request()->except([$name, 'page']) as $key => $val)
            @if(is_scalar($val) && filled($val))
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endif
        @endforeach
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input
                type="search"
                name="{{ $name }}"
                value="{{ $value }}"
                @if($model) x-model="{{ $model }}" @endif
                placeholder="{{ $placeholder }}"
                aria-label="{{ $placeholder }}"
                class="w-full bg-white rounded-lg border border-[#4ade80] pl-9 pr-3.5 py-2 text-[13px] text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#22c55e] focus:ring-2 focus:ring-green-500/20 transition"
            >
        </div>
    </form>
@else
    <div {{ $attributes->merge(['class' => 'relative ' . $widthClass]) }}>
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
            <x-icon name="search" class="w-4 h-4" />
        </span>
        <input
            type="search"
            name="{{ $name }}"
            value="{{ $value }}"
            @if($model) x-model="{{ $model }}" @endif
            placeholder="{{ $placeholder }}"
            aria-label="{{ $placeholder }}"
            class="w-full bg-white rounded-lg border border-[#4ade80] pl-9 pr-3.5 py-2 text-[13px] text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#22c55e] focus:ring-2 focus:ring-green-500/20 transition"
        >
    </div>
@endif
