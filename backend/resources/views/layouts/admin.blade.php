<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title>{{ $title ?? 'Admin' }} · Founders Table</title>
  <link rel="icon" href="/assets/img/favicon.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" />
  <link rel="stylesheet" href="/admin-assets/admin.css?v=3" />
</head>
<body class="admin-body" x-data="{ nav: false }">
  <svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="i-grid" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
    <symbol id="i-mic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10a7 7 0 0 0 14 0M12 17v5M8 22h8"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6.5 6.5 0 0 1 3.5 5.5"/></symbol>
    <symbol id="i-settings" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></symbol>
    <symbol id="i-logout" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 17l5-5-5-5M15 12H3"/></symbol>
    <symbol id="i-external" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
    <symbol id="i-download" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11M7 10l5 5 5-5M5 20h14"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></symbol>
    <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></symbol>
    <symbol id="i-back" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></symbol>
  </svg>

  <aside class="sidebar" :class="{ open: nav }">
    <a class="brand" href="{{ route('admin.dashboard') }}" wire:navigate>
      <img src="/assets/img/founders-table-mark.png" alt="" width="44" height="44" />
      <span><strong>Founders Table</strong><small>Admin</small></span>
    </a>

    <livewire:admin.sidebar-nav />

    <div class="side-foot">
      <a href="{{ route('home') }}" target="_blank" rel="noopener"><svg class="i"><use href="#i-external" /></svg><span>View website</span></a>
      <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit"><svg class="i"><use href="#i-logout" /></svg><span>Sign out</span></button>
      </form>
    </div>
  </aside>
  <div class="scrim" x-show="nav" x-transition.opacity @click="nav = false"></div>

  <div class="main">
    <header class="topbar">
      <button class="menu-btn" type="button" @click="nav = true" aria-label="Open menu"><svg class="i"><use href="#i-menu" /></svg></button>
      <div class="topbar-title">{{ $title ?? 'Dashboard' }}</div>
      <div class="user-chip">
        <span class="avatar">{{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}</span>
        <span class="user-name">{{ auth()->user()->name }}</span>
      </div>
    </header>

    <main class="content">
      {{ $slot }}
    </main>
  </div>

  <div class="toast" x-data="{ show: @js(session()->has('toast')), text: @js(session('toast', '')) }"
       x-init="if (show) window.__t = setTimeout(() => show = false, 2600)"
       x-on:toast.window="text = $event.detail.message; show = true; clearTimeout(window.__t); window.__t = setTimeout(() => show = false, 2600)"
       x-show="show" x-transition x-cloak role="status" x-text="text"></div>
</body>
</html>
