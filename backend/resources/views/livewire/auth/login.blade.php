<div class="auth-card">
  <img class="auth-logo" src="/assets/img/founders-table-logo.webp" alt="The Founders Table by RAG Holdings" width="120" height="117" />
  <p class="eyebrow">Admin</p>
  <h1>Sign in to the dashboard</h1>
  <p class="muted">Guest applications and suggestions from the Founders Table website.</p>

  <form wire:submit="login" class="form" novalidate>
    <label class="field">
      <span>Email</span>
      <input type="email" wire:model="email" autocomplete="username" autofocus required @class(['invalid' => $errors->has('email')]) />
      @error('email') <small class="error">{{ $message }}</small> @enderror
    </label>

    <label class="field">
      <span>Password</span>
      <input type="password" wire:model="password" autocomplete="current-password" required @class(['invalid' => $errors->has('password')]) />
      @error('password') <small class="error">{{ $message }}</small> @enderror
    </label>

    <label class="check">
      <input type="checkbox" wire:model="remember" />
      <span>Keep me signed in</span>
    </label>

    <button type="submit" class="btn btn-gold btn-block" wire:loading.attr="disabled">
      <span wire:loading.remove wire:target="login">Sign in</span>
      <span wire:loading wire:target="login">Signing in…</span>
    </button>
  </form>

  <a class="auth-back" href="{{ route('home') }}">← Back to the website</a>
</div>
