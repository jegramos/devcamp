<?php

namespace App\Http\Controllers;

use App\Enums\SessionFlashKey;
use App\Http\Requests\AccountSettingsRequest;
use App\Models\User;
use App\Services\PasskeyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
use Webauthn\Exception\InvalidDataException;

class AccountSettingsController
{
    /**
     * @throws InvalidDataException
     */
    public function index(AccountSettingsRequest $request, PasskeyService $passkeyService): Response
    {
        /** @var User $user */
        $user = $request->user();
        $destroyPasskeyUrl = route('passkeys.destroy', ['passkey' => 1]);
        $destroyPasskeyUrl = explode('/1', $destroyPasskeyUrl)[0];

        $passkeyRegisterOptions = $passkeyService->createRegisterOptions($user);
        Session::flash(SessionFlashKey::CMS_PASSKEY_REGISTER_OPTIONS->value, $passkeyRegisterOptions);

        return Inertia::render('Account/AccountSettingsPage', [
            'storeAccountSettingsUrl' => route('accountSettings.store'),
            'createPasskeyUrl' => route('passkeys.store'),
            'destroyPasskeyUrl' => $destroyPasskeyUrl,
            'currentSettings' => $user->accountSettings->data,
            'passkeyRegisterOptions' => $passkeyService->serialize($passkeyRegisterOptions),
            'passkeys' => $user
                ->passkeys()
                ->latest()
                ->get()
                ->map(fn ($passkey) => [
                    'id' => $passkey->id,
                    'name' => $passkey->name,
                    'created_at' => $passkey->created_at->diffForHumans(['options' => Carbon::JUST_NOW]),
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
