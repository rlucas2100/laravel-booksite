{{-- The page numbers under a list. Laravel hands us $paginator and $elements. --}}
@if ($paginator->hasPages())
    <nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Pages">
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm opacity-40" aria-disabled="true"><x-icon name="arrow-left" size="h-4 w-4"/> Back</span>
        @else
            <a class="btn btn-sm" href="{{ $paginator->previousPageUrl() }}" rel="prev"><x-icon name="arrow-left" size="h-4 w-4"/> Back</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-1 font-bold text-soft">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="btn btn-sm btn-sun" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="btn btn-sm" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="btn btn-sm" href="{{ $paginator->nextPageUrl() }}" rel="next">Next <x-icon name="arrow-right" size="h-4 w-4"/></a>
        @else
            <span class="btn btn-sm opacity-40" aria-disabled="true">Next <x-icon name="arrow-right" size="h-4 w-4"/></span>
        @endif
    </nav>
@endif
