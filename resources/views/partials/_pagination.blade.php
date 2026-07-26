@if ($paginator->hasPages())
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <span class="pulse-muted">Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results</span>
        <div style="display:flex; gap:8px;">
            @if ($paginator->onFirstPage())
                <span class="pulse-btn light" style="opacity:.5; cursor:not-allowed;"><i class="fas fa-chevron-left"></i> Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="pulse-btn light"><i class="fas fa-chevron-left"></i> Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="pulse-btn light">Next <i class="fas fa-chevron-right"></i></a>
            @else
                <span class="pulse-btn light" style="opacity:.5; cursor:not-allowed;">Next <i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
@endif
