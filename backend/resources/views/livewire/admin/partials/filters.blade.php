{{-- Shared toolbar: status tabs + search + sort + export. Expects $statuses, $counts, $total, $exportUrl, $placeholder, optional $showSource. --}}
<div class="status-tabs" role="tablist">
  <button type="button" wire:click="$set('status', '')" @class(['on' => $status === ''])>All <b>{{ $total }}</b></button>
  @foreach ($statuses as $key => $label)
    <button type="button" wire:click="$set('status', '{{ $key }}')" @class(['on' => $status === $key])>{{ $label }} <b>{{ $counts[$key] ?? 0 }}</b></button>
  @endforeach
</div>

<div class="toolbar">
  <label class="search">
    <svg class="i"><use href="#i-search" /></svg>
    <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ $placeholder }}" aria-label="Search" />
  </label>
  @if ($showSource ?? false)
    <select wire:model.live="source" aria-label="Form">
      <option value="">All forms</option>
      <option value="section">Page form</option>
      <option value="popup">Pop-up</option>
    </select>
  @endif
  <select wire:model.live="sort" aria-label="Sort">
    <option value="newest">Newest first</option>
    <option value="oldest">Oldest first</option>
  </select>
  <a class="btn btn-ghost" href="{{ $exportUrl }}"><svg class="i"><use href="#i-download" /></svg> Export CSV</a>
</div>
