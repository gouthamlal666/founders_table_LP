<div class="page">
  <div class="page-head">
    <div>
      <p class="eyebrow">Suggest a Guest form</p>
      <h1>Guest suggestions</h1>
      <p class="muted">Founders your audience wants to see at the table.</p>
    </div>
  </div>

  @include('livewire.admin.partials.filters', ['placeholder' => 'Search founder, company, location, suggester…'])

  <div class="card table-card" wire:loading.class="loading" wire:target="search,status,sort,gotoPage,nextPage,previousPage">
    @if ($suggestions->isEmpty())
      <div class="empty">
        @if ($search !== '' || $status !== '')
          No suggestions match these filters. <button type="button" class="link" wire:click="clearFilters">Clear filters</button>
        @else
          No suggestions yet. They’ll appear here when visitors use “Suggest a Guest” on the website.
        @endif
      </div>
    @else
      <table class="table">
        <thead>
          <tr>
            <th>Founder</th>
            <th class="hide-md">Industry</th>
            <th class="hide-sm">Suggested by</th>
            <th>Status</th>
            <th class="hide-sm">Received</th>
            <th><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($suggestions as $s)
            <tr wire:key="sug-{{ $s->id }}">
              <td>
                <a class="cell-main" href="{{ route('admin.suggestions.show', $s) }}" wire:navigate>
                  <strong>{{ $s->founder_name }}</strong>
                  <small>{{ $s->company_name }}{{ $s->location ? ' · '.$s->location : '' }}</small>
                </a>
              </td>
              <td class="hide-md">{{ $s->industry ?: '—' }}</td>
              <td class="hide-sm">
                {{ $s->suggester_name ?: 'Anonymous' }}
                @if ($s->connection)<small class="muted block">{{ $s->connection }}</small>@endif
              </td>
              <td>
                <select class="status-select status-{{ $s->status }}" aria-label="Status for {{ $s->founder_name }}"
                        wire:change="setStatus({{ $s->id }}, $event.target.value)">
                  @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected($s->status === $key)>{{ $label }}</option>
                  @endforeach
                </select>
              </td>
              <td class="hide-sm muted small" title="{{ $s->created_at->format('j M Y, H:i') }}">{{ $s->created_at->diffForHumans() }}</td>
              <td class="cell-actions">
                <a class="icon-btn" href="{{ $s->linkedin }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn of {{ $s->founder_name }}">in</a>
                <a class="icon-btn" href="{{ route('admin.suggestions.show', $s) }}" wire:navigate aria-label="Open {{ $s->founder_name }}"><svg class="i"><use href="#i-arrow" /></svg></a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{ $suggestions->links('livewire.admin.partials.pagination') }}
</div>
