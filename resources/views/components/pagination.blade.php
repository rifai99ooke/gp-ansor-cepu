@props([
    'paginator',
])

@if($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        // Display up to 9 page numbers centered around currentPage
        $maxPages = 9;
        $half = (int) floor($maxPages / 2);
        $start = max(1, $currentPage - $half);
        $end = min($lastPage, $start + $maxPages - 1);
        if ($end - $start + 1 < $maxPages) {
            $start = max(1, $end - $maxPages + 1);
        }
    @endphp

    <nav role="navigation" aria-label="Navigasi Halaman" class="flex items-center justify-center gap-1.5 select-none">
        {{-- Previous Page Link --}}
        @if($paginator->onFirstPage())
            <span
                aria-disabled="true"
                aria-label="Halaman sebelumnya"
                class="w-7 h-7 rounded-md bg-white border border-gray-200 text-gray-300 flex items-center justify-center text-xs cursor-not-allowed"
            >
                <x-icon name="chevron-left" class="w-3.5 h-3.5" />
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                rel="prev"
                aria-label="Halaman sebelumnya"
                class="w-7 h-7 rounded-md bg-white border border-[#d1d5db] text-gray-700 hover:bg-gray-50 hover:border-green-500 flex items-center justify-center text-xs transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
            >
                <x-icon name="chevron-left" class="w-3.5 h-3.5" />
            </a>
        @endif

        {{-- Page Numbers --}}
        @for($page = $start; $page <= $end; $page++)
            @if($page === $currentPage)
                <span
                    aria-current="page"
                    class="w-7 h-7 rounded-md bg-[#16a34a] border border-[#16a34a] text-white font-semibold flex items-center justify-center text-[12px]"
                >
                    {{ $page }}
                </span>
            @else
                <a
                    href="{{ $paginator->url($page) }}"
                    aria-label="Halaman {{ $page }}"
                    class="w-7 h-7 rounded-md bg-white border border-[#d1d5db] text-gray-700 hover:bg-gray-50 hover:border-green-500 font-medium flex items-center justify-center text-[12px] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
                >
                    {{ $page }}
                </a>
            @endif
        @endfor

        {{-- Next Page Link --}}
        @if($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                rel="next"
                aria-label="Halaman berikutnya"
                class="w-7 h-7 rounded-md bg-white border border-[#d1d5db] text-gray-700 hover:bg-gray-50 hover:border-green-500 flex items-center justify-center text-xs transition focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500"
            >
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
            </a>
        @else
            <span
                aria-disabled="true"
                aria-label="Halaman berikutnya"
                class="w-7 h-7 rounded-md bg-white border border-gray-200 text-gray-300 flex items-center justify-center text-xs cursor-not-allowed"
            >
                <x-icon name="chevron-right" class="w-3.5 h-3.5" />
            </span>
        @endif
    </nav>
@endif
