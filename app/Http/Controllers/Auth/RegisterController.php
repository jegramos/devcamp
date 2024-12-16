<?php

namespace App\Http\Controllers\Auth;

use App\Actions\GetCountryListAction;
use App\Actions\User\CreateUserAction;
use App\Http\Requests\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class RegisterController
{
    public function showForm(GetCountryListAction $getCountryListAction): Response
    {
        $recaptchaEnabled = config('services.google.recaptcha.enabled');
        $checkAvailabilityBaseUrl = route('api.checkAvailability', ['type' => 1, 'value' => 1]);
        $checkAvailabilityBaseUrl = explode('/1/1', $checkAvailabilityBaseUrl)[0];

        return Inertia::render('Auth/RegisterPage', [
            'loginUrl' => route('auth.login.showForm'),
            'processRegistrationUrl' => route('auth.register.process'),
            'checkAvailabilityBaseUrl' => $checkAvailabilityBaseUrl,
            'countryOptions' => $getCountryListAction->execute('id', 'name'),
            'recaptchaEnabled' => $recaptchaEnabled,
            'recaptchaSiteKey' => $recaptchaEnabled ? config('services.google.recaptcha.site_key') : null,
            'loginViaGoogleUrl' => route('oauth.google.redirect'),
            'loginViaGithubUrl' => route('oauth.github.redirect'),
            'resumeBuilderUrl' => route('builder.resume.index'),
        ]);
    }

    /**
     * Create and log-in the created user.
     *
     * @throws Throwable
     */
    public function process(RegisterRequest $request, CreateUserAction $createUserAction): RedirectResponse
    {
        $userInfo = [
            'email' => $request->validated('email'),
            'username' => $request->validated('username'),
            'password' => $request->validated('password'),
            'given_name' => $request->validated('given_name'),
            'family_name' => $request->validated('family_name'),
        ];

        $user = $createUserAction->execute($userInfo);
        Auth::login($user);
        $request->session()->regenerate();

        Event::dispatch(new Registered($user));

        return redirect()->route('builder.resume.index');
    }
}
