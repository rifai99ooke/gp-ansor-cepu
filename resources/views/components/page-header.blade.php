@props([
    'title',
    'subtitle' => null,
    'variant' => 'centered', // 'centered' | 'uppercase' | 'green-underline'
])

@if($variant === 'uppercase')
    <div {{ $attributes->merge(['class' => 'text-center']) }}>
        <h1 class="text-[32px] sm:text-[40px] font-extrabold text-[#181e2b] uppercase tracking-tight leading-tight">
            {{ $title }}
        </h1>
        <div class="w-[50px] h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mx-auto mt-2.5"></div>
        @if(filled($subtitle))
            <p class="mt-3.5 text-[15px] sm:text-[16px] text-gray-600 max-w-2xl mx-auto">
                {{ $subtitle }}
            </p>
        @endif
    </div>
@elseif($variant === 'green-underline')
    <div {{ $attributes->merge(['class' => 'text-center']) }}>
        <div class="inline-block">
            <h1 class="text-[32px] sm:text-[40px] font-bold text-[#065f46] leading-tight">
                {{ $title }}
            </h1>
            <div class="mt-1.5 h-[3px] w-full rounded-full bg-linear-to-r from-[#059669] to-[#34d399]"></div>
        </div>
        @if(filled($subtitle))
            <p class="mt-3.5 text-[15px] sm:text-[16px] text-gray-700 max-w-2xl mx-auto">
                {{ $subtitle }}
            </p>
        @endif
    </div>
@else
    <div {{ $attributes->merge(['class' => 'text-center']) }}>
        <h1 class="text-[34px] sm:text-[44px] font-extrabold text-[#181e2b] tracking-tight leading-tight">
            {{ $title }}
        </h1>
        <div class="w-20 h-[3px] rounded-full bg-linear-to-r from-green-500 to-cyan-500 mx-auto mt-3"></div>
        @if(filled($subtitle))
            <p class="mt-4 text-[16px] sm:text-[18px] text-gray-700 max-w-2xl mx-auto">
                {{ $subtitle }}
            </p>
        @endif
    </div>
@endif
