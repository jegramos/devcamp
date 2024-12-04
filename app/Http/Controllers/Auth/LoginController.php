<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ErrorCode;
use App\Enums\SessionFlashKey;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\PasskeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class LoginController
{
    public function showForm(PasskeyService $passkeyService): Response
    {
        $passkeyAuthenticateOptions = $passkeyService->createAuthenticateOptions();
        Session::flash(SessionFlashKey::CMS_PASSKEY_AUTHENTICATE_OPTIONS->value, $passkeyAuthenticateOptions);

        return Inertia::render('Auth/LoginPage', [
            'registerUrl' => route('auth.register.showForm'),
            'authenticateUrl' => route('auth.login.authenticate'),
            'loginViaPasskeyUrl' => route('passkeys.login'),
            'loginViaGoogleUrl' => route('oauth.google.redirect'),
            'loginViaGithubUrl' => route('oauth.github.redirect'),
            'forgotPasswordUrl' => route('auth.password.showForgotPasswordForm'),
            'resumeBuilderUrl' => route('builder.resume.index'),
            'passkeyAuthenticateOptions' => $passkeyService->serialize($passkeyAuthenticateOptions),
        ]);
    }

    public function authenticate(LoginRequest $request): RedirectResponse
    {
        $email = $request->input('email');
        $username = $request->input('username');
        $password = $request->input('password');
        $remember = $request->input('remember', false);

        if ($email) {
            $user = User::getViaEmailAndPassword($email, $password);
        } else {
            $user = User::getViaUsernameAndPassword($username, $password);
        }

        if (!$user) {
            return redirect()->back()->withErrors([
                ErrorCode::INVALID_CREDENTIALS->value => 'The provided credentials do not match our records.',
            ]);
        }

        if ($user->accountSettings->passkeysEnabled()) {
            return redirect()->back()->withErrors([
                ErrorCode::BAD_REQUEST->value => 'Password-less login has been configured. Please login via Passkeys.',
            ]);
        }

        if (!$user->active) {
            return redirect()->back()->withErrors([
                ErrorCode::ACCOUNT_DEACTIVATED->value => 'Your account has been deactivated.',
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        return redirect()->intended(route('builder.resume.index'));
    }
}
