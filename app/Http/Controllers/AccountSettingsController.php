<?php

namespace App\Http\Controllers;

use App\Enums\SessionFlashKey;
use App\Http\Requests\AccountSettingsRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class AccountSettingsController
{
    public function index(AccountSettingsRequest $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $destroyPasskeyUrl = route('accountSettings.passkeys.destroy', ['passkey' => 1]);
        $destroyPasskeyUrl = explode('/1', $destroyPasskeyUrl)[0];

        return Inertia::render('Account/AccountSettingsPage', [
            'storeAccountSettingsUrl' => route('accountSettings.store'),
            'createPasskeyUrl' => route('accountSettings.passkeys.store'),
            'destroyPasskeyUrl' => $destroyPasskeyUrl,
            'currentSettings' => $user->accountSettings->data,
            'passkeys' => $user
                ->passkeys()
                ->latest()
                ->get()
                ->map(fn ($passkey) => [
                    'id' => $passkey->id,
                    'name' => $passkey->name,
                    'created_at' => $passkey->created_at->diffForHumans(),
                ])
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(AccountSettingsRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            /** @var User $user */
            $user = $request->user();
            $settings = $user->accountSettings->data;

            if ($request->has('theme')) {
                $settings['theme'] = $request->input('theme');
            }

            if ($request->has('passkeys_enabled')) {
                $settings['passkeys_enabled'] = $request->input('passkeys_enabled');
            }

            if ($request->has('2fa_enabled')) {
                $settings['2fa_enabled'] = $request->input('2fa_enabled');
            }

            $user->accountSettings()->update(['data' => $settings]);
        });

        return redirect()
            ->back()
            ->with(SessionFlashKey::CMS_SUCCESS->value, 'Account settings have been updated.');
    }
}
