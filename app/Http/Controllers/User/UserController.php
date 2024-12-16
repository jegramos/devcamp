<?php

namespace App\Http\Controllers\User;

use App\Actions\GetCountryListAction;
use App\Actions\User\CreateUserAction;
use App\Actions\User\UpdateUserAction;
use App\Enums\SessionFlashKey;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserListItemResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class UserController
{
    public function index(UserRequest $request, GetCountryListAction $getCountryListAction): Response
    {
        $users = User::sorted($request)
            ->filtered()
            ->with('userProfile', 'roles')
            ->paginate(12)
            ->withQueryString()
            ->through(function ($user) {
                return UserListItemResource::make($user);
            });

        $checkAvailabilityBaseUrl = route('api.checkAvailability', ['type' => 1, 'value' => 1]);
        $checkAvailabilityBaseUrl = explode('/1/1', $checkAvailabilityBaseUrl)[0];
        $updateUserUrl = route('users.update', ['user' => 1]);
        $updateUserUrl = explode('/1', $updateUserUrl)[0];
        $deleteUserUrl = route('users.destroy', ['user' => 1]);
        $deleteUserUrl = explode('/1', $deleteUserUrl)[0];

        return Inertia::render('Admin/UserManagementPage', [
            'users' => $users,
            'currentFilters' => (object) $request->validated(),
            'totalFiltersActive' => count($request->validated()),
            'checkAvailabilityBaseUrl' => $checkAvailabilityBaseUrl,
            'storeUserUrl' => route('users.store'),
            'updateUserUrl' => $updateUserUrl,
            'deleteUserUrl' => $deleteUserUrl,
            'countryOptions' => Inertia::defer(fn () => $getCountryListAction->execute('id', 'name')),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(UserRequest $request, CreateUserAction $createUserAction): RedirectResponse
    {
        $user = $createUserAction->execute($request->validated());
        $user->sendAccountVerificationNotification($request->input('password'));
        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, 'User created successfully.');
    }

    /**
     * @throws Throwable
     */
    public function update(User $user, UserRequest $request, UpdateUserAction $updateUserAction): RedirectResponse
    {
        $updateInfo = $request->validated();
        if (isset($updateInfo['verified']) && $updateInfo['verified']) {
            $updateInfo['email_verified_at'] = now();
        } else {
            $updateInfo['email_verified_at'] = null;
        }

        unset($updateInfo['verified']);

        $updateUserAction->execute($user, $updateInfo);
        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();
        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, 'User deleted successfully.');
    }
}
