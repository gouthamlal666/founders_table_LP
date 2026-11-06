<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Landing-page forms: 8 submissions per 10 minutes per visitor IP (same as the old Node server).
        RateLimiter::for('submissions', fn (Request $request) => Limit::perMinutes(10, 8)
            ->by($request->ip())
            ->response(fn () => response()->json(['ok' => false, 'error' => 'Too many submissions. Please try again later.'], 429)));
    }
}
