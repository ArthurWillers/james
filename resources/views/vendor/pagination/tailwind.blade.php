@if ($paginator->hasPages())
    <div class="pt-4 flex flex-col md:flex-row justify-between items-center gap-4">
        @if ($paginator->total() > 0)
            <div class="text-neutral-500 text-sm font-medium whitespace-nowrap text-center md:text-left">
                {!! __('Showing') !!} {{ $paginator->firstItem() }} {!! __('to') !!} {{ $paginator->lastItem() }} {!! __('of') !!} {{ $paginator->total() }} {!! __('results') !!}
            </div>
        @else
            <div></div>
        @endif

        <div class="flex flex-wrap justify-center items-center bg-white border border-neutral-200 rounded-[8px] p-[2px] shadow-sm">
            @if ($paginator->onFirstPage())
                <div aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="flex justify-center items-center size-9 sm:size-8 rounded-[6px] text-neutral-300">
                    <x-heroicon-m-chevron-left class="w-4 h-4 rtl:hidden" />
                    <x-heroicon-m-chevron-right class="w-4 h-4 hidden rtl:inline" />
                </div>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}" class="flex justify-center items-center size-9 sm:size-8 rounded-[6px] text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 transition-colors">
                    <x-heroicon-m-chevron-left class="w-4 h-4 rtl:hidden" />
                    <x-heroicon-m-chevron-right class="w-4 h-4 hidden rtl:inline" />
                </a>
            @endif

            <div class="hidden sm:flex sm:flex-wrap items-center justify-center">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <div aria-disabled="true" class="cursor-default flex justify-center items-center text-sm size-8 rounded-[6px] font-medium text-neutral-400">
                            {{ $element }}
                        </div>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <div aria-current="page" class="cursor-default flex justify-center items-center text-sm h-8 px-3 rounded-[6px] font-bold text-[var(--color-accent)] bg-transparent">
                                    {{ $page }}
                                </div>
                            @else
                                <a href="{{ $url }}" class="flex justify-center items-center text-sm h-8 px-3 rounded-[6px] text-neutral-500 font-medium hover:bg-neutral-100 hover:text-neutral-800 transition-colors" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}" class="flex justify-center items-center size-9 sm:size-8 rounded-[6px] text-neutral-500 hover:bg-neutral-100 hover:text-neutral-800 transition-colors">
                    <x-heroicon-m-chevron-right class="w-4 h-4 rtl:hidden" />
                    <x-heroicon-m-chevron-left class="w-4 h-4 hidden rtl:inline" />
                </a>
            @else
                <div aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="flex justify-center items-center size-9 sm:size-8 rounded-[6px] text-neutral-300">
                    <x-heroicon-m-chevron-right class="w-4 h-4 rtl:hidden" />
                    <x-heroicon-m-chevron-left class="w-4 h-4 hidden rtl:inline" />
                </div>
            @endif
        </div>
    </div>
@endif
