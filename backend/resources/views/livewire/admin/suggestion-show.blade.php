@php $s = $suggestion; @endphp
<div class="page">
  <a class="back" href="{{ route('admin.suggestions') }}" wire:navigate><svg class="i"><use href="#i-back" /></svg> All suggestions</a>

  <div class="detail-hero">
    <span class="initials initials-lg initials-gold">{{ mb_substr($s->founder_name, 0, 1) }}</span>
    <div>
      <p class="eyebrow">Guest suggestion</p>
      <h1>{{ $s->founder_name }}</h1>
      <p class="muted">{{ $s->founder_role ? $s->founder_role.', ' : '' }}{{ $s->company_name }}{{ $s->location ? ' · '.$s->location : '' }}</p>
    </div>
    <div class="hero-actions">
      <a class="btn btn-gold" href="{{ $s->linkedin }}" target="_blank" rel="noopener noreferrer">Open LinkedIn</a>
      @if ($s->suggester_email)
        <a class="btn btn-ghost" href="mailto:{{ $s->suggester_email }}">Email suggester</a>
      @endif
    </div>
  </div>

  <div class="detail-grid">
    <div class="card">
      <h2 class="card-title">Why this founder</h2>
      <p class="prose">{{ $s->why }}</p>

      @if ($s->topics)
        <h2 class="card-title spaced">Topics to cover</h2>
        <div class="tags">
          @foreach (array_filter(array_map('trim', explode(',', $s->topics))) as $topic)
            <span class="tag">{{ $topic }}</span>
          @endforeach
        </div>
      @endif

      @if ($s->questions)
        <h2 class="card-title spaced">Questions to ask</h2>
        <p class="prose">{{ $s->questions }}</p>
      @endif

      <h2 class="card-title spaced">Details</h2>
      <dl class="fields">
        <div><dt>Company</dt><dd>{{ $s->company_name }}</dd></div>
        <div><dt>Role</dt><dd>{{ $s->founder_role ?: '—' }}</dd></div>
        <div><dt>Industry</dt><dd>{{ $s->industry ?: '—' }}</dd></div>
        <div><dt>Based in</dt><dd>{{ $s->location ?: '—' }}</dd></div>
        <div><dt>Website / Instagram</dt><dd>{{ $s->company_website ?: '—' }}</dd></div>
        <div><dt>LinkedIn</dt><dd><a href="{{ $s->linkedin }}" target="_blank" rel="noopener noreferrer">{{ \Illuminate\Support\Str::limit(preg_replace('#^https?://(www\.)?#', '', $s->linkedin), 48) }}</a></dd></div>
        <div><dt>Suggested by</dt><dd>{{ $s->suggester_name ?: 'Anonymous' }}</dd></div>
        <div><dt>Their email</dt><dd>@if ($s->suggester_email)<a href="mailto:{{ $s->suggester_email }}">{{ $s->suggester_email }}</a>@else — @endif</dd></div>
        <div><dt>Connection</dt><dd>{{ $s->connection ?: '—' }}</dd></div>
      </dl>
    </div>

    @include('livewire.admin.partials.record-side', ['record' => $s, 'kind' => 'suggestion'])
  </div>
</div>
