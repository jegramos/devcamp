<?php

use App\Enums\Permission;
use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Auth\GithubLoginController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\PasskeyLoginController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Builder\ResumeBuilderController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PasskeyController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\UserController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

/**
 * Routes defined in this group are accessible on the root CMS domain (app.devcamp.site).
 */
$cmsDomain = parse_url(Config::get('app.url'), PHP_URL_HOST);
Route::domain($cmsDomain)->group(function () {
    Route::get('', LandingPageController::class)
        ->middleware('guest')
        ->name('landingPage');

    Route::get('/about', AboutPageController::class)
        ->middleware(['auth', 'verified'])
        ->name('aboutPage');

    // Authentication Routes
    Route::name('auth.')->group(function () {
        Route::middleware('guest')
            ->controller(LoginController::class)
            ->name('login.')
            ->group(function () {
                /** @uses LoginController::showForm */
                Route::get('/login', 'showForm')->name('showForm');

                /** @uses LoginController::authenticate */
                Route::post('/login', 'authenticate')
                    ->middleware('throttle:login')
                    ->name('authenticate')
                    ->middleware(HandlePrecognitiveRequests::class);
            });

        Route::middleware('auth')
            ->controller(LogoutController::class)
            ->name('logout.')
            ->group(function () {
                /** @uses LogoutController::logoutCurrent */
                Route::post('/logout', 'logoutCurrent')->name('current');

                /** @uses LogoutController::logoutOtherDevices */
                Route::post('/logout/other-devices', 'logoutOtherDevices')->name('otherDevices');
            });

        Route::middleware('guest')
            ->name('register.')
            ->controller(RegisterController::class)
            ->group(function () {
                /** @uses RegisterController::showForm */
                Route::get('/register', 'showForm')->name('showForm');

                /** @uses RegisterController::process */
                Route::post('/register', 'process')->name('process');
            });

        Route::middleware('guest')
            ->controller(ForgotPasswordController::class)
            ->name('password.')
            ->group(function () {
                /** @uses ForgotPasswordController::showForgotPasswordForm */
                Route::get('/forgot-password', 'showForgotPasswordForm')
                    ->name('showForgotPasswordForm');

                /** @uses ForgotPasswordController::sendResetLink */
                Route::post('/forgot-password', 'sendResetLink')
                    ->name('sendResetLink');

                /** @uses ForgotPasswordController::showResetPasswordForm */
                Route::get('/forgot-password/reset', 'showResetPasswordForm')
                    ->name('showResetPasswordForm');

                /** @uses ForgotPasswordController::resetPassword */
                Route::patch('/forgot-password/reset', 'resetPassword')
                    ->name('resetPassword');
            });
    });

    // OAuth / OIDC Routes
    Route::middleware('guest')->prefix('oauth')->name('oauth.')->group(function () {
        Route::name('google.')
            ->controller(GoogleLoginController::class)
            ->group(function () {
                /** @uses GoogleLoginController::redirect */
                Route::get('/google/redirect', 'redirect')->name('redirect');

                /** @uses GoogleLoginController::callback */
                Route::get('/google/callback', 'callback')->name('callback');
            });

        Route::name('github.')
            ->controller(GithubLoginController::class)
            ->group(function () {
                /** @uses GithubLoginController::redirect */
                Route::get('/github/redirect', 'redirect')->name('redirect');

                /** @uses GithubLoginController::callback */
                Route::get('/github/callback', 'callback')->name('callback');
            });
    });

    // Passkeys
    Route::prefix('passkeys')->name('passkeys.')->group(function () {
        Route::post('', [PasskeyController::class, 'store'])
            ->middleware(['auth', 'verified'])
            ->name('store');

        Route::post('login', PasskeyLoginController::class)
            ->middleware('guest')
            ->name('login');

        Route::delete('{passkey}', [PasskeyController::class, 'destroy'])
            ->middleware(['auth', 'verified'])
            ->name('destroy');
    });

    // Verify Email Routes
    Route::controller(VerifyEmailController::class)
        ->name('verification.')
        ->group(function () {
            /** @uses VerifyEmailController::showNotice */
            Route::get('/email/verify', 'showNotice')
                ->middleware('auth')
                ->name('notice');

            /** @uses VerifyEmailController::verify */
            Route::get('/email/verify/{id}/{hash}', 'verify')
                ->middleware('signed')
                ->name('verify');

            /** @uses VerifyEmailController::sendVerification */
            Route::post('/email/verification-notification', 'sendVerification')
                ->middleware(['auth', 'throttle:send-email-notification'])
                ->name('send');
        });

    // Profile Routes
    Route::controller(ProfileController::class)
        ->name('profile.')
        ->group(function () {
            /** @uses ProfileController::index */
            Route::get('/profile', 'index')
                ->middleware(['auth', 'verified'])
                ->name('index');

            /** @uses ProfileController::update */
            Route::patch('/profile', 'update')
                ->middleware(['auth', 'verified'])
                ->name('update');

            /** @uses ProfileController::uploadProfilePicture */
            Route::post('/profile/upload/profile-picture', 'uploadProfilePicture')
                ->middleware(['auth', 'verified'])
                ->name('uploadProfilePicture');

            /** @uses ProfileController::sendEmailUpdateConfirmation */
            Route::post('/profile/email-update/notify', 'sendEmailUpdateConfirmation')
                ->middleware(['auth', 'verified', 'throttle:send-email-notification'])
                ->name('sendEmailUpdateConfirmation');

            /** @uses ProfileController::confirmEmailUpdate */
            Route::get('/profile/email-update/confirm/{user}/{email}', 'confirmEmailUpdate')
                ->middleware('signed')
                ->name('confirmEmailUpdate');

            /** @uses ProfileController::changePassword */
            Route::patch('/profile/change-password', 'changePassword')
                ->middleware(['auth', 'verified'])
                ->name('changePassword');
        });

    // Account Settings Routes
    Route::middleware(['auth', 'verified'])
        ->controller(AccountSettingsController::class)
        ->prefix('account-settings')
        ->name('accountSettings.')
        ->group(function () {
            Route::get('', 'index')->name('index');
            Route::post('', 'store')->name('store');
        });

    // User Routes
    Route::middleware(['auth', 'verified'])
        ->prefix('users')
        ->name('users.')
        ->controller(UserController::class)
        ->group(function () {
            Route::get('', 'index')
                ->middleware('can:' . Permission::VIEW_USERS->value)
                ->name('index');

            Route::post('', 'store')
                ->middleware('can:' . Permission::CREATE_USERS->value)
                ->name('store');

            Route::patch('{user}', 'update')
                ->middleware('can:' . Permission::UPDATE_USERS->value)
                ->name('update');

            Route::delete('{user}', 'destroy')
                ->middleware('can:' . Permission::DELETE_USERS->value)
                ->name('destroy');
        });

    // Builder Routes
    Route::middleware(['auth', 'verified'])->prefix('builder')->name('builder.')->group(function () {
        // Resume
        Route::prefix('resume')->name('resume.')
            ->controller(ResumeBuilderController::class)->group(function () {
                /** @uses ResumeBuilderController::index */
                Route::get('', 'index')->name('index');

                /** @uses ResumeBuilderController::storeSubdomain */
                Route::post('subdomain', 'storeSubdomain')->name('storeSubdomain');

                /** @uses ResumeBuilderController::storeContent */
                Route::post('content', 'storeContent')->name('storeContent');
            });
    });
});
