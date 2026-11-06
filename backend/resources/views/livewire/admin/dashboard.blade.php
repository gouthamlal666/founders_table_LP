<div class="page">
  <div class="page-head">
    <div>
      <p class="eyebrow">Overview</p>
      <h1>Welcome back, {{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</h1>
      <p class="muted">Everything that came in through the Founders Table website.</p>
    </div>
  </div>

  {{-- KPI cards --}}
  <section class="kpis">
    <a class="kpi" href="{{ route('admin.applications', ['status' => 'new']) }}" wire:navigate>
      <span class="kpi-label">New applications</span>
      <strong class="kpi-value">{{ $this->stats['newApplications'] }}</strong>
      <span class="kpi-foot">{{ $this->stats['applications'] }} total</span>
    </a>
    <a class="kpi" href="{{ route('admin.suggestions', ['status' => 'new']) }}" wire:navigate>
      <span class="kpi-label">New suggestions</span>
      <strong class="kpi-value">{{ $this->stats['newSuggestions'] }}</strong>
      <span class="kpi-foot">{{ $this->stats['suggestions'] }} total</span>
    </a>
    <div class="kpi">
      <span class="kpi-label">Last 7 days</span>
      <strong class="kpi-value">{{ $this->stats['week'] }}</strong>
      @php $d = $this->stats['weekDelta']; @endphp
      <span @class(['kpi-foot', 'up' => $d > 0, 'down' => $d < 0])>
        {{ $d > 0 ? '▲ '.$d : ($d < 0 ? '▼ '.abs($d) : 'No change') }} vs previous week
      </span>
    </div>
    <a class="kpi kpi-dark" href="{{ route('admin.applications', ['status' => 'scheduled']) }}" wire:navigate>
      <span class="kpi-label">Scheduled &amp; recorded</span>
      <strong class="kpi-value">{{ $this->stats['scheduled'] }}</strong>
      <span class="kpi-foot">guests at the table</span>
    </a>
  </section>

  <section class="grid-2">
    {{-- Submissions chart --}}
    <div class="card chart-card">
      <div class="card-head">
        <div>
          <h2>Submissions</h2>
          <p class="muted small">{{ $this->chart['total'] }} in the last {{ $range }} days</p>
        </div>
        <div class="seg" role="group" aria-label="Chart range">
          @foreach ([7, 30, 90] as $r)
            <button type="button" wire:click="$set('range', {{ $r }})" @class(['on' => $range === $r])>{{ $r }}d</button>
          @endforeach
        </div>
      </div>
      <div class="legend"><span class="dot dot-apps"></span>Applications <span class="dot dot-sugg"></span>Suggestions</div>
      <div class="bars" style="--n: {{ count($this->chart['days']) }}" wire:loading.class="loading" wire:target="range">
        @foreach ($this->chart['days'] as $day)
          @php $total = $day['apps'] + $day['sugg']; @endphp
          <div class="bar" title="{{ $day['date']->format('D j M') }}: {{ $day['apps'] }} applications, {{ $day['sugg'] }} suggestions">
            <span class="bar-sugg" style="height: {{ $day['sugg'] / $this->chart['max'] * 100 }}%"></span>
            <span class="bar-apps" style="height: {{ $day['apps'] / $this->chart['max'] * 100 }}%"></span>
          </div>
        @endforeach
      </div>
      <div class="bars-axis">
        <span>{{ $this->chart['days']->first()['date']->format('j M') }}</span>
        <span>Today</span>
      </div>
    </div>

    {{-- Pipeline --}}
    <div class="card">
      <div class="card-head"><h2>Application pipeline</h2></div>
      @php $pipeMax = max(1, $this->pipeline->max('total')); @endphp
      <ul class="pipeline">
        @foreach ($this->pipeline as $stage)
          <li>
            <a href="{{ route('admin.applications', ['status' => $stage['key']]) }}" wire:navigate>
              <span class="status status-{{ $stage['key'] }}">{{ $stage['label'] }}</span>
              <span class="pipe-track"><span style="width: {{ $stage['total'] / $pipeMax * 100 }}%"></span></span>
              <b>{{ $stage['total'] }}</b>
            </a>
          </li>
        @endforeach
      </ul>
      <div class="split-stats">
        <div><span class="muted small">From the page form</span><strong>{{ $this->sources['section'] }}</strong></div>
        <div><span class="muted small">From the pop-up</span><strong>{{ $this->sources['popup'] }}</strong></div>
      </div>
    </div>
  </section>

  <section class="grid-2">
    <div class="card">
      <div class="card-head">
        <h2>Latest applications</h2>
        <a class="link" href="{{ route('admin.applications') }}" wire:navigate>View all <svg class="i"><use href="#i-arrow" /></svg></a>
      </div>
      @forelse ($this->latestApplications as $a)
        <a class="row-link" href="{{ route('admin.applications.show', $a) }}" wire:navigate>
          <span class="initials">{{ mb_substr($a->full_name, 0, 1) }}</span>
          <span class="row-main"><strong>{{ $a->full_name }}</strong><small>{{ $a->company }}{{ $a->industry ? ' · '.$a->industry : '' }}</small></span>
          <span class="status status-{{ $a->status }}">{{ $a->statusLabel() }}</span>
          <time class="muted small" datetime="{{ $a->created_at->toIso8601String() }}">{{ $a->created_at->diffForHumans(short: true) }}</time>
        </a>
      @empty
        <p class="empty">No applications yet. They’ll appear here as soon as someone fills in “Be a Guest”.</p>
      @endforelse
    </div>

    <div class="card">
      <div class="card-head">
        <h2>Latest suggestions</h2>
        <a class="link" href="{{ route('admin.suggestions') }}" wire:navigate>View all <svg class="i"><use href="#i-arrow" /></svg></a>
      </div>
      @forelse ($this->latestSuggestions as $s)
        <a class="row-link" href="{{ route('admin.suggestions.show', $s) }}" wire:navigate>
          <span class="initials initials-gold">{{ mb_substr($s->founder_name, 0, 1) }}</span>
          <span class="row-main"><strong>{{ $s->founder_name }}</strong><small>{{ $s->company_name }} · by {{ $s->suggester_name ?: 'Anonymous' }}</small></span>
          <span class="status status-{{ $s->status }}">{{ $s->statusLabel() }}</span>
          <time class="muted small" datetime="{{ $s->created_at->toIso8601String() }}">{{ $s->created_at->diffForHumans(short: true) }}</time>
        </a>
      @empty
        <p class="empty">No suggestions yet. They’ll appear here when visitors use “Suggest a Guest”.</p>
      @endforelse

      @if ($this->industries->isNotEmpty())
        <div class="tags-block">
          <p class="muted small">Top applicant industries</p>
          <div class="tags">
            @foreach ($this->industries as $row)
              <span class="tag">{{ $row->industry }} <b>{{ $row->total }}</b></span>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </section>
</div>
