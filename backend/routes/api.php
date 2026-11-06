<?php

use App\Http\Controllers\Api\SubmissionController;
use Illuminate\Support\Facades\Route;

// Landing-page forms. Rate limit "submissions" is defined in AppServiceProvider.
Route::middleware('throttle:submissions')->group(function () {
    Route::post('/podcast-applications', [SubmissionController::class, 'application']);
    Route::post('/guest-suggestions', [SubmissionController::class, 'suggestion']);
});
