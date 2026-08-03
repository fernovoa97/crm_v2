@if ($paginator->hasPages())
<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="crm-pagination">

    @if ($paginator->onFirstPage())
        <span class="crm-page-link crm-page-arrow disabled" aria-disabled="true">‹</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="crm-page-link crm-page-arrow">‹</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="crm-page-link crm-page-dots">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="crm-page-link active" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="crm-page-link" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="crm-page-link crm-page-arrow">›</a>
    @else
        <span class="crm-page-link crm-page-arrow disabled" aria-disabled="true">›</span>
    @endif

</nav>
@endif