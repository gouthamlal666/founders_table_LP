<?php

use App\Http\Controllers\Admin\ExportController;
use App\Livewire\Admin\Applications;
use App\Livewire\Admin\ApplicationShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Settings;
use App\Livewire\Admin\SuggestionShow;
use App\Livewire\Admin\Suggestions;
use App\Livewire\Auth\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// The public landing page (index.html one folder up). Its /assets are served from public/assets.
Route::get('/', function () {
    return response()->file(config('services.founders.landing_path').'/index.html', [
        'Content-Type' => 'text/html; charset=utf-8',
        'Cache-Control' => 'no-cache',
    ]);
})->name('home');

Route::middleware('guest')->group(function () {
    Route::livewire('/admin/login', Login::class)->name('login');
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::livewire('/', Dashboard::class)->name('dashboard');
    Route::livewire('/applications', Applications::class)->name('applications');
    Route::livewire('/applications/{application}', ApplicationShow::class)->name('applications.show');
    Route::livewire('/suggestions', Suggestions::class)->name('suggestions');
    Route::livewire('/suggestions/{suggestion}', SuggestionShow::class)->name('suggestions.show');
    Route::livewire('/settings', Settings::class)->name('settings');

    Route::get('/export/{type}', ExportController::class)
        ->whereIn('type', ['applications', 'suggestions'])
        ->name('export');

    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
