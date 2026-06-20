@if ($paginator->hasPages())
    <div class="pt-4 flex justify-between items-center gap-3">
        <div></div>

        <div class="flex items-center bg-white border border-neutral-200 rounded-[8px] p-[2px] shadow-sm">
            @if ($paginator->onFirstPage())
                <div aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="flex justify-center items-center size-9 sm:size-8 rounded-[6px] text-neutral-300">
                    <x-icons.heroicons.mini.chevron-left class="w-4 h-4 rtl:hidden" />
                    <x-icons.heroicons.mini.chevron-right class="w-4 h-4 hidden rtl:inline" />
                </div>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="flex justify-center items-center size-9 sm:size-8 rounded-[6px] text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 transition-colors">
                    <x-icons.heroicons.mini.chevron-left class="w-4 h-4 rtl:hidden" />
                    <x-icons.heroicons.mini.chevron-right class="w-4 h-4 hidden rtl:inline" />
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="flex justify-center items-center size-9 sm:size-8 rounded-[6px] text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 transition-colors">
                    <x-icons.heroicons.mini.chevron-right class="w-4 h-4 rtl:hidden" />
                    <x-icons.heroicons.mini.chevron-left class="w-4 h-4 hidden rtl:inline" />
                </a>
            @else
                <div aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="flex justify-center items-center size-9 sm:size-8 rounded-[6px] text-neutral-300">
                    <x-icons.heroicons.mini.chevron-right class="w-4 h-4 rtl:hidden" />
                    <x-icons.heroicons.mini.chevron-left class="w-4 h-4 hidden rtl:inline" />
                </div>
            @endif
        </div>
    </div>
@endif
