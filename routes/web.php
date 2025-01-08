<?php

use Illuminate\Support\Facades\Route;

/** Load all Portfolio-related routes */
Route::prefix('')->group(base_path('routes/portfolio.routes.php'));

/** Load all CMS-related routes */
Route::prefix('')->group(base_path('routes/cms.routes.php'));
