<?php

namespace App\Http\Controllers;

use App\Enums\SessionFlashKey;
use App\Http\Requests\PasskeyRequest;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PasskeyController
{
    /**
     * @throws ValidationException
     */
    public function store(PasskeyRequest $request): RedirectResponse
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
            throw ValidationException::withMessages(['name' => 'This name already exists.']);
        }

        // Max of 5 active passkeys
        if ($user->passkeys()->count('id') >= 5) {
            throw ValidationException::withMessages(['name' => 'You can only have 5 active passkeys.']);
        }

        $user->passkeys()->create([
            'name' => $name,
            'data' => [],
            'credential_id' => \Str::random(),
        ]);

        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, 'Passkey has been created.');
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
