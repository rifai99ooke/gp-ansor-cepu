@props([
    'title',
])

<div {{ $attributes->merge(['class' => 'inline-flex flex-col']) }}>
    <div class="bg-linear-to-r from-gray-100 to-gray-50 border-l-2 border-gray-300 px-3 py-1">
        <h2 class="text-[20px] sm:text-[22px] font-bold leading-snug bg-linear-to-r from-[#17272d] to-[#119148] bg-clip-text text-transparent">
            {{ $title }}
        </h2>
    </div>
    <div class="h-[2px] w-full bg-[#16a34a] mt-1"></div>
</div>
