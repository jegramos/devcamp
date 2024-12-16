<?php

namespace App\Http\Controllers\User;

use App\Actions\GetCountryListAction;
use App\Actions\User\UpdateUserAction;
use App\Enums\SessionFlashKey;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Models\User;
use App\Notifications\ConfirmEmailUpdateNotification;
use App\Services\CloudStorageManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ProfileController
{
    public function index(GetCountryListAction $getCountryListAction): Response
    {
        $checkAvailabilityBaseUrl = route('api.checkAvailability', ['type' => 1, 'value' => 1]);
        $checkAvailabilityBaseUrl = explode('/1/1', $checkAvailabilityBaseUrl)[0];
        return Inertia::render('Account/ProfilePage', [
            'profile' => new UserProfileResource(Auth::user()->load('userProfile')),
            'countryOptions' => $getCountryListAction->execute('id', 'name'),
            'updateProfileUrl' => route('profile.update'),
            'checkAvailabilityBaseUrl' => $checkAvailabilityBaseUrl,
            'uploadProfilePictureUrl' => route('profile.uploadProfilePicture'),
            'sendEmailUpdateConfirmationUrl' => route('profile.sendEmailUpdateConfirmation'),
            'changePasswordUrl' => route('profile.changePassword'),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function update(ProfileRequest $request, UpdateUserAction $updateAction): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $updateAction->execute($user, $request->validated());
        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, 'Profile updated successfully.');
    }

    /**
     * Upload Profile Picture
     * @throws Throwable
     */
    public function uploadProfilePicture(ProfileRequest $request, UpdateUserAction $updateAction, CloudStorageManager $cloudStorage): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $file = $request->file('photo');

        $path = "images/$user->id/profile-pictures";
        $extension = $file->getClientOriginalExtension();
        $fileName = "$user->id-profile-picture.$extension";

        $fullPath = $cloudStorage->upload($path, $file, $fileName);
        $updateAction->execute($user, ['profile_picture_path' => $fullPath]);

        return redirect()
            ->back()
            ->with(SessionFlashKey::CMS_SUCCESS->value, 'Profile picture updated successfully.');
    }

    public function sendEmailUpdateConfirmation(ProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $newEmail = $request->input('email');

        // Users created from external accounts are not allowed to change their email
        if ($user->isFromExternalAccount()) {
            $provider = Str::title($user->externalAccount->provider->value);
            abort(403, 'You cannot request an email change since your account is bound to ' . $provider);
        }

        defer(function () use ($user, $newEmail) {
            Notification::route('mail', $newEmail)->notify(new ConfirmEmailUpdateNotification($user, $newEmail));
        });

        return redirect()
            ->back()
            ->with(SessionFlashKey::CMS_SUCCESS->value, 'Update email confirmation notification sent.');
    }

    /**
     * @throws Throwable
     */
    public function confirmEmailUpdate(User $user, string $encryptedNewEmail, UpdateUserAction $updateAction): RedirectResponse
    {
        $email = Crypt::decrypt($encryptedNewEmail);
        $updateAction->execute($user, ['email' => $email]);

        return redirect()
            ->route(Auth::check() ? 'profile.index' : 'auth.login.showForm')
            ->with(SessionFlashKey::CMS_EMAIL_UPDATE_CONFIRMED->value, 'Email updated successfully.');
    }

    public function changePassword(ProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Users created from external accounts are not allowed to change their email
        if ($user->isFromExternalAccount()) {
            $provider = Str::title($user->externalAccount->provider->value);
            abort(403, 'You cannot change your password since your account is bound to ' . $provider);
        }

        $newPassword = $request->get('password');
        $user->password = $newPassword;
        $user->save();

        return redirect()
            ->back()
            ->with(SessionFlashKey::CMS_SUCCESS->value, 'Password updated successfully.');
    }
}
