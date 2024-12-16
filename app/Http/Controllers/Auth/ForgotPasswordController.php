<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ErrorCode;
use App\Enums\SessionFlashKey;
use App\Http\Requests\ForgotPasswordRequest;
use App\Models\User;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController
{
    public function showForgotPasswordForm(): Response
    {
        return Inertia::render('Auth/ForgotPasswordPage', [
            'sendResetLinkUrl' => route('auth.password.sendResetLink'),
            'loginUrl' => route('auth.login.showForm'),
        ]);
    }

    public function sendResetLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $email = $request->get('email');
        $user = User::query()->where('email', $email)->first();

        // Do nothing if there is no user found with the email or is deactivated
        if (!$user || !$user->active) {
            return redirect()->back()
                ->with(SessionFlashKey::CMS_SUCCESS->value, 'Password reset link has been sent to your email.');
        }

        if ($user->isFromExternalAccount()) {
            return redirect()->back()->withErrors([
                ErrorCode::BAD_REQUEST->value => "You can't reset password for an external account."
            ]);
        }

        $userId = $user->id;
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_THROTTLED) {
            $seconds = Config::get('auth.passwords.users.throttle');
            $message = "You may only request passwords reset request every $seconds seconds.";
            throw new ThrottleRequestsException($message);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            Log::error('Unable to send reset password link', [
                'user_id' => $userId,
                'email' => $email,
                'status' => $status,
            ]);
            return redirect()->back()->withErrors([ErrorCode::SERVER->value => 'Unable to send reset password link.']);
        }

        return redirect()
            ->back()
            ->with(SessionFlashKey::CMS_SUCCESS->value, 'Password reset link has been sent to your email.');
    }

    public function showResetPasswordForm(ForgotPasswordRequest $request): Response|RedirectResponse
    {
        $email = $request->input('email');
        $token = $request->input('token');

        if (!$email || !$token) {
            abort(403, 'Invalid password reset link.');
        }

        return Inertia::render('Auth/ResetPasswordPage', [
            'resetPasswordUrl' => route('auth.password.resetPassword'),
            'emailParam' => $email,
            'tokenParam' => $token,
        ]);
    }

    public function resetPassword(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = $password;
                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return redirect()
                ->back()
                ->withErrors([
                    ErrorCode::BAD_REQUEST->value => 'This password reset link is no longer valid. Please request a new one.',
                ]);
        }

        return redirect()
            ->route('auth.login.showForm')
            ->with(SessionFlashKey::CMS_SUCCESS->value, "You've successfully changed your password.");
    }
}
