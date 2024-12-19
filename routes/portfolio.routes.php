<?php

use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/**
 * Routes defined in this group are accessible on portfolio subdomains.
 *
 * Example: juan.works.devcamp.site
 *
 * The `{account}` placeholder in the route definition will capture the subdomain name
 * (e.g., "juan" in the example).
 */
$portfolioSubdomainRoot = Config::get('app.portfolio_subdomain');
Route::domain("{account}.$portfolioSubdomainRoot")->group(function () {
    Route::controller(PortfolioController::class)->group(function () {
        /** @uses PortfolioController::index */
        Route::get('', 'index')->name('portfolio.index');
    });
});
