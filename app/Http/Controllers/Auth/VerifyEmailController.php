<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ErrorCode;
use App\Enums\SessionFlashKey;
use App\Http\Requests\NoLoginEmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonInterval;
use Config;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VerifyEmailController
{
    /**
     * @throws Exception
     */
    public function showNotice(): Response
    {
        $emailExpirationInMinutes = Config::get('auth.verification.expire');
        $interval = CarbonInterval::minutes($emailExpirationInMinutes);
        $emailExpiration = $interval->cascade()->forHumans();

        return Inertia::render('Auth/VerifyEmailNoticePage', [
            'sendEmailVerificationUrl' => route('verification.send'),
            'emailVerificationExpiration' => $emailExpiration,
            'resumeBuilderUrl' => route('builder.resume.index'),
        ]);
    }

    public function verify(NoLoginEmailVerificationRequest $request): RedirectResponse
    {

        $request->fulfill();
        return redirect()
            ->route(Auth::check() ? 'builder.resume.index' : 'auth.login.showForm')
            ->with(SessionFlashKey::CMS_EMAIL_VERIFIED->value, 'Email address verified.');
    }

    public function sendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()
                ->back()
                ->withErrors([ErrorCode::EMAIL_ALREADY_VERIFIED->value => 'Email address already verified.']);
        }

        $request->user()->sendEmailVerificationNotification();

        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, 'Verification email sent.');
    }
}
