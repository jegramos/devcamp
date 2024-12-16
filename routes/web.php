<?php

use Illuminate\Support\Facades\Route;

/** Load all CMS-related routes */
Route::prefix('')->group(base_path('routes/cms.routes.php'));
