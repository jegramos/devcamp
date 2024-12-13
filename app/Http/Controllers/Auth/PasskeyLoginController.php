<?php

namespace App\Http\Controllers\Auth;

use App\Enums\ErrorCode;
use App\Enums\SessionFlashKey;
use App\Http\Requests\PasskeyLoginRequest;
use App\Models\Passkey;
use App\Services\PasskeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\Exception\AuthenticatorResponseVerificationException;
use Webauthn\PublicKeyCredential;

class PasskeyLoginController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(PasskeyLoginRequest $request, PasskeyService $passkeyService): RedirectResponse
    {
        $publicKeyCredential = $passkeyService->deserialize($request->input('answer'), PublicKeyCredential::class);
        if (!($publicKeyCredential->response instanceof AuthenticatorAssertionResponse)) {
            return redirect()->back()->withErrors([ErrorCode::INVALID_CREDENTIALS->value => 'The passkey is invalid.']);
        }

        $passkey = Passkey::query()->where('credential_id', $publicKeyCredential->rawId)->first();
        if (!$passkey) {
            return redirect()->back()->withErrors([ErrorCode::INVALID_CREDENTIALS->value => 'The passkey is invalid.']);
        }

        if (!$passkey->user->accountSettings->passkeysEnabled()) {
            return redirect()->back()->withErrors([
                ErrorCode::BAD_REQUEST->value => 'Passkey login is disabled. Please login via email (or username) and password.',
            ]);
        }


        if (!$passkey->user->active) {
            return redirect()->back()->withErrors([
                ErrorCode::ACCOUNT_DEACTIVATED->value => 'Your account has been deactivated.',
            ]);
        }

        try {
            $publicKeyCredentialSource = $passkeyService->verifyPublicKeyCredential(
                requestHost: $request->getHost(),
                publicKeyCredentialSource: $passkey->data,
                publicKeyCredentialRequestOptions: Session::get(SessionFlashKey::CMS_PASSKEY_AUTHENTICATE_OPTIONS->value),
                assertionResponse: $publicKeyCredential->response
            );
        } catch (AuthenticatorResponseVerificationException) {
            return redirect()->back()->withErrors([
                ErrorCode::INVALID_CREDENTIALS->value => 'There was a problem authenticating with your passkey.',
            ]);
        }

        $passkey->update(['data' => $publicKeyCredentialSource]);

        Auth::loginUsingId($passkey->user_id);
        $request->session()->regenerate();

        return redirect()
            ->route('builder.resume.index')
            ->with(SessionFlashKey::CMS_LOGIN_SUCCESS->value, 'Logged in via Passkey.');
    }
}
