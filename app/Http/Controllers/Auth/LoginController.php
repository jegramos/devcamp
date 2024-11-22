<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ErrorCode;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController
{
    public function showForm(): Response
    {
        return Inertia::render('Auth/LoginPage', [
            'registerUrl' => route('auth.register.showForm'),
            'authenticateUrl' => route('auth.login.authenticate'),
            'loginViaGoogleUrl' => route('oauth.google.redirect'),
            'loginViaGithubUrl' => route('oauth.github.redirect'),
            'forgotPasswordUrl' => route('auth.password.showForgotPasswordForm'),
            'resumeBuilderUrl' => route('builder.resume.index'),
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
