<?php

use App\Http\Controllers\User\CheckAvailabilityController;
use Illuminate\Support\Facades\Route;

// Check for user email or username availability
Route::get('v1/availability/{type}/{value}/{excludedId?}', CheckAvailabilityController::class)
    ->name('api.checkAvailability')
    ->whereIn('type', ['email', 'username', 'mobile_number']);
