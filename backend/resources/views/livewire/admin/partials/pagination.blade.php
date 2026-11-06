@if ($paginator->hasPages())
  <nav class="pager" aria-label="Pagination">
    <span class="muted small">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
    <div class="pager-btns">
      <button type="button" wire:click="previousPage" @disabled($paginator->onFirstPage())>Previous</button>
      <span class="small">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
      <button type="button" wire:click="nextPage" @disabled(! $paginator->hasMorePages())>Next</button>
    </div>
  </nav>
@endif
