<br>
@if($paginator->hasPages())
<nav role="navigation" aria-label="Pagination Navigation">
    <ul class="pagination">
        @if ($paginator->onFirstPage())
        <li class="page-item disabled" aria-disabled="true">
            <span class="page-link">{{translate('pagination:previous')}}</span>
        </li>
        @else
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                {{translate('pagination:previous')}}
            </a>
        </li>
        @endif
        @if ($paginator->hasMorePages())
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">{{translate('pagination:next')}}</a>
        </li>
        @else
        <li class="page-item disabled" aria-disabled="true">
            <span class="page-link">{{translate('pagination:next')}}</span>
        </li>
        @endif
    </ul>
</nav>
@endif