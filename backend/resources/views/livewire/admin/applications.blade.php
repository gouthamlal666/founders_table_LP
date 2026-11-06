<div class="page">
  <div class="page-head">
    <div>
      <p class="eyebrow">Be a Guest form</p>
      <h1>Guest applications</h1>
      <p class="muted">Founders who want to come on the podcast. Change a status right from the list.</p>
    </div>
  </div>

  @include('livewire.admin.partials.filters', ['placeholder' => 'Search name, company, email, phone…', 'showSource' => true])

  <div class="card table-card" wire:loading.class="loading" wire:target="search,status,sort,source,gotoPage,nextPage,previousPage">
    @if ($applications->isEmpty())
      <div class="empty">
        @if ($search !== '' || $status !== '' || $source !== '')
          No applications match these filters. <button type="button" class="link" wire:click="clearFilters">Clear filters</button>
        @else
          No applications yet. They’ll appear here as soon as someone fills in “Be a Guest” on the website.
        @endif
      </div>
    @else
      <table class="table">
        <thead>
          <tr>
            <th>Applicant</th>
            <th>Contact</th>
            <th class="hide-md">Industry</th>
            <th class="hide-md">Form</th>
            <th>Status</th>
            <th class="hide-sm">Received</th>
            <th><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($applications as $a)
            <tr wire:key="app-{{ $a->id }}">
              <td>
                <a class="cell-main" href="{{ route('admin.applications.show', $a) }}" wire:navigate>
                  <strong>{{ $a->full_name }}</strong>
                  <small>{{ $a->company }}{{ $a->role ? ' · '.$a->role : '' }}</small>
                </a>
              </td>
              <td class="cell-contact">
                <a href="mailto:{{ $a->email }}">{{ $a->email }}</a>
                <a href="tel:{{ $a->phone }}" class="muted">{{ $a->phone }}</a>
              </td>
              <td class="hide-md">{{ $a->industry ?: '—' }}</td>
              <td class="hide-md"><span class="pill">{{ $a->source === 'popup' ? 'Pop-up' : 'Page' }}</span></td>
              <td>
                <select class="status-select status-{{ $a->status }}" aria-label="Status for {{ $a->full_name }}"
                        wire:change="setStatus({{ $a->id }}, $event.target.value)">
                  @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected($a->status === $key)>{{ $label }}</option>
                  @endforeach
                </select>
              </td>
              <td class="hide-sm muted small" title="{{ $a->created_at->format('j M Y, H:i') }}">{{ $a->created_at->diffForHumans() }}</td>
              <td class="cell-actions">
                <a class="icon-btn" href="{{ route('admin.applications.show', $a) }}" wire:navigate aria-label="Open {{ $a->full_name }}"><svg class="i"><use href="#i-arrow" /></svg></a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{ $applications->links('livewire.admin.partials.pagination') }}
</div>
