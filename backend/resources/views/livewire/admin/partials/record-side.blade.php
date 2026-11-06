{{-- Right-hand panel on a detail page: status, internal notes, delete. Expects $statuses, $record, $kind. --}}
<aside class="detail-side">
  <div class="card">
    <h2 class="card-title">Status</h2>
    <select class="status-select status-{{ $status }} wide" wire:model.live="status" aria-label="Status">
      @foreach ($statuses as $key => $label)
        <option value="{{ $key }}">{{ $label }}</option>
      @endforeach
    </select>
    <dl class="meta">
      <div><dt>Received</dt><dd>{{ $record->created_at->format('j M Y, H:i') }}</dd></div>
      <div><dt>Last updated</dt><dd>{{ $record->updated_at->diffForHumans() }}</dd></div>
      @if ($record->page_url)
        <div><dt>Sent from</dt><dd class="truncate" title="{{ $record->page_url }}">{{ parse_url($record->page_url, PHP_URL_PATH) ?: '/' }}{{ parse_url($record->page_url, PHP_URL_FRAGMENT) ? '#'.parse_url($record->page_url, PHP_URL_FRAGMENT) : '' }}</dd></div>
      @endif
    </dl>
  </div>

  <form class="card" wire:submit="saveNotes">
    <h2 class="card-title">Internal notes</h2>
    <p class="muted small">Only admins see these.</p>
    <textarea wire:model="notes" rows="6" placeholder="Call notes, follow-ups, recording date…"></textarea>
    @error('notes') <small class="error">{{ $message }}</small> @enderror
    <button type="submit" class="btn btn-navy btn-block" wire:loading.attr="disabled" wire:target="saveNotes">
      <span wire:loading.remove wire:target="saveNotes">Save notes</span>
      <span wire:loading wire:target="saveNotes">Saving…</span>
    </button>
  </form>

  <button type="button" class="btn btn-danger-ghost btn-block"
          wire:click="delete" wire:confirm="Delete this {{ $kind }} permanently? This can’t be undone.">
    Delete {{ $kind }}
  </button>
</aside>
