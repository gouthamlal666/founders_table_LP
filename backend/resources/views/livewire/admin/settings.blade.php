<div class="page narrow">
  <div class="page-head">
    <div>
      <p class="eyebrow">Account</p>
      <h1>Your account</h1>
      <p class="muted">Add more admins from the server with <code>php artisan admin:create</code>.</p>
    </div>
  </div>

  <form class="card form" wire:submit="saveProfile">
    <h2 class="card-title">Profile</h2>
    <label class="field">
      <span>Name</span>
      <input type="text" wire:model="name" autocomplete="name" @class(['invalid' => $errors->has('name')]) />
      @error('name') <small class="error">{{ $message }}</small> @enderror
    </label>
    <label class="field">
      <span>Email</span>
      <input type="email" wire:model="email" autocomplete="email" @class(['invalid' => $errors->has('email')]) />
      @error('email') <small class="error">{{ $message }}</small> @enderror
    </label>
    <button type="submit" class="btn btn-navy" wire:loading.attr="disabled" wire:target="saveProfile">Save profile</button>
  </form>

  <form class="card form" wire:submit="savePassword">
    <h2 class="card-title">Change password</h2>
    <label class="field">
      <span>Current password</span>
      <input type="password" wire:model="current_password" autocomplete="current-password" @class(['invalid' => $errors->has('current_password')]) />
      @error('current_password') <small class="error">{{ $message }}</small> @enderror
    </label>
    <label class="field">
      <span>New password <small class="muted">(at least 10 characters)</small></span>
      <input type="password" wire:model="password" autocomplete="new-password" @class(['invalid' => $errors->has('password')]) />
      @error('password') <small class="error">{{ $message }}</small> @enderror
    </label>
    <label class="field">
      <span>Confirm new password</span>
      <input type="password" wire:model="password_confirmation" autocomplete="new-password" />
    </label>
    <button type="submit" class="btn btn-navy" wire:loading.attr="disabled" wire:target="savePassword">Change password</button>
  </form>
</div>
