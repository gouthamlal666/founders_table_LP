@php $a = $application; @endphp
<div class="page">
  <a class="back" href="{{ route('admin.applications') }}" wire:navigate><svg class="i"><use href="#i-back" /></svg> All applications</a>

  <div class="detail-hero">
    <span class="initials initials-lg">{{ mb_substr($a->full_name, 0, 1) }}</span>
    <div>
      <p class="eyebrow">Guest application · {{ $a->source === 'popup' ? 'Pop-up' : 'Page form' }}</p>
      <h1>{{ $a->full_name }}</h1>
      <p class="muted">{{ $a->role ? $a->role.', ' : '' }}{{ $a->company }}</p>
    </div>
    <div class="hero-actions">
      <a class="btn btn-gold" href="mailto:{{ $a->email }}?subject={{ rawurlencode('Founders Table podcast — your application') }}">Email</a>
      @if (strlen($a->whatsappNumber()) >= 7)
        <a class="btn btn-ghost" href="https://wa.me/{{ $a->whatsappNumber() }}" target="_blank" rel="noopener">WhatsApp</a>
      @endif
      <a class="btn btn-ghost" href="tel:{{ $a->phone }}">Call</a>
    </div>
  </div>

  <div class="detail-grid">
    <div class="card">
      <h2 class="card-title">Details</h2>
      <dl class="fields">
        <div><dt>Email</dt><dd><a href="mailto:{{ $a->email }}">{{ $a->email }}</a></dd></div>
        <div><dt>Phone</dt><dd><a href="tel:{{ $a->phone }}">{{ $a->phone }}</a></dd></div>
        <div><dt>Company</dt><dd>{{ $a->company }}</dd></div>
        <div><dt>Role</dt><dd>{{ $a->role ?: '—' }}</dd></div>
        <div><dt>Industry</dt><dd>{{ $a->industry ?: '—' }}</dd></div>
        <div><dt>LinkedIn / website</dt><dd>
          @if ($a->linkedin)<a href="{{ $a->linkedin }}" target="_blank" rel="noopener noreferrer">{{ \Illuminate\Support\Str::limit(preg_replace('#^https?://(www\.)?#', '', $a->linkedin), 48) }}</a>@else — @endif
        </dd></div>
      </dl>

      <h2 class="card-title spaced">Their story</h2>
      <p class="prose">{{ $a->story ?: 'They didn’t add a story.' }}</p>
    </div>

    @include('livewire.admin.partials.record-side', ['record' => $a, 'kind' => 'application'])
  </div>
</div>
