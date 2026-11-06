<nav class="side-nav" aria-label="Admin">
  @foreach ($items as $item)
    <a href="{{ route($item['route']) }}" wire:navigate
       @class(['active' => $current === $item['match'] || str_starts_with($current, $item['match'].'.')])>
      <svg class="i"><use href="#i-{{ $item['icon'] }}" /></svg>
      <span>{{ $item['label'] }}</span>
      @if ($item['badge'])<b class="nav-badge" title="New">{{ $item['badge'] }}</b>@endif
    </a>
  @endforeach
</nav>
