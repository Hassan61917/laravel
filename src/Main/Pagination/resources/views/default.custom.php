<br>
@if ($paginator->hasPages())
<nav class="d-flex justify-items-center justify-content-between">
    <div class="d-flex justify-content-between flex-fill d-sm-none">
        <ul class="pagination">
            @if ($paginator->onFirstPage())
            <li class="page-item disabled" aria-disabled="true">
                <span class="page-link">{{translate('pagination:previous')}}</span>
            </li>
            @else
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">{{translate('pagination:previous')}}</a>
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
    </div>

    <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between">
        <div>
            <p class="small text-muted">
                {{translate("pagination:showing")}}
                <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
                {{translate("pagination:to")}}
                <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
                {{translate("pagination:of")}}
                <span class="fw-semibold">{{ $paginator->total() }}</span>
                {{translate("pagination:results")}}
            </p>
        </div>

        <div>
            <ul class="pagination">
                @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="{{translate('pagination:previous')}}">
                    <span class="page-link" aria-hidden="true">&lsaquo;</span>
                </li>
                @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{translate('pagination:previous')}}">&lsaquo;</a>
                </li>
                @endif

                @foreach ($elements as $element)
                @if (is_string($element))
                <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                @else
                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
                @endforeach
                @endif
                @endforeach
                @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{translate('pagination:next')}}">&rsaquo;</a>
                </li>
                @else
                <li class="page-item disabled" aria-disabled="true" aria-label="{{translate('pagination:next')}}">
                    <span class="page-link" aria-hidden="true">&rsaquo;</span>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>
@endif