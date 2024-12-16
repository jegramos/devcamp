<?php

namespace App\Http\Controllers;

use App\Enums\SessionFlashKey;
use App\Http\Requests\PasskeyRequest;
use App\Models\Passkey;
use App\Models\User;
use App\Services\PasskeyService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Throwable;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\PublicKeyCredential;

class PasskeyController
{
    public function store(PasskeyRequest $request, PasskeyService $passkeyService): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Externally created users cannot set passkeys
        if ($user->isFromExternalAccount()) {
            abort(403, 'External accounts cannot manage passkeys.');
        }

        // Cannot have duplicate names
        $name = $request->input('name');
        if ($user->passkeys()->where('name', $name)->exists()) {
            return redirect()->back()->withErrors(['name' => 'This name already exists.']);
        }

        // Max of 5 active passkeys
        if ($user->passkeys()->count('id') >= 5) {
            return redirect()->back()->withErrors(['name' => 'You can only have 5 active passkeys.']);
        }

        $publicKeyCredential = $passkeyService->deserialize($request->input('passkey'), PublicKeyCredential::class);
        if (!($publicKeyCredential->response instanceof AuthenticatorAttestationResponse)) {
            return redirect()->back()->withErrors(['name' => 'Invalid passkey.']);
        }

        try {
            $publicKeyCredentialSource = $passkeyService->registerPublicKeyCredential(
                requestHost: $request->getHost(),
                publicKeyCredentialCreationOptions: Session::get(SessionFlashKey::CMS_PASSKEY_REGISTER_OPTIONS->value),
                attestationResponse: $publicKeyCredential->response
            );
        } catch (Throwable) {
            return redirect()->back()->withErrors(['name' => 'The given passkey is invalid.']);
        }

        $user->passkeys()->create([
            'name' => $name,
            'data' => $publicKeyCredentialSource
        ]);

        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, "Passkey \"$name\" has been created.");
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Passkey $passkey): RedirectResponse
    {
        $name = $passkey->name;

        Gate::authorize('destroy', $passkey);

        $passkey->delete();
        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, "Passkey '$name' has been deleted.");
    }
}
