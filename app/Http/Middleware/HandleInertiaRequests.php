<?php

namespace App\Http\Middleware;

use App\Enums\ExternalLoginProvider;
use App\Enums\Permission;
use App\Enums\SessionFlashKey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'appName' => config('app.name'),
            'logoutUrl' => auth()->user() ? route('auth.logout.current') : null,
            'pageUris' => [
                'portfolio.resume' => route(name: 'builder.resume.index', absolute: false),
                'misc.about' => route(name: 'aboutPage', absolute: false),
                'misc.notifications' => route(name: 'notifications.index', absolute: false),
                'account.profile' => route(name: 'profile.index', absolute: false),
                'account.settings' => route(name: 'accountSettings.index', absolute: false),
                'admin.userManagement' => route(name: 'users.index', absolute: false),
            ],
            'accountSettings' => Auth::check() ? Auth::user()->accountSettings->data : null,
            'auth.can' => [
                'view_users' => Auth::check() && Auth::user()->can(Permission::VIEW_USERS->value),
            ],
            'auth.user' => function () {
                if (!Auth::check()) {
                    return null;
                }

                /** @var User $user */
                $user = Auth::user()->load('userProfile', 'roles', 'externalAccount');
                return [
                    'email_verified' => (bool) $user->email_verified_at,
                    'username' => $user->username,
                    'nameInitials' => $user->userProfile->given_name[0] . '' . $user->userProfile->family_name[0],
                    'email' => $user->email,
                    'full_name' => $user->userProfile->full_name,
                    'profile_picture_url' => $user->userProfile->profile_picture_url,
                    'roles' => $user
                        ->roles
                        ->pluck('name')
                        ->map(fn (string $role) => Str::title(str_replace('_', ' ', $role)))
                        ->toArray(),
                    'provider_name' => $user->externalAccount()->exists() ? Str::title($user->externalAccount->provider->value) : null,
                    'from_external_account' => $user->isFromExternalAccount(),
                    'recommend_username_change' => function () use ($user) {
                        // Check if the user has a default username set when
                        // their account was created via an External Login provider
                        $usernameArray = explode('-', $user->username);
                        $usernameFirstPartIsProviderName = in_array($usernameArray[0], ExternalLoginProvider::toArray()); // Ex. 'github'
                        $usernameSecondPartIsUser = isset($usernameArray[1]) && $usernameArray[1] === 'user';
                        $userNeverUpdated = $user->created_at->eq($user->updated_at);

                        return $user->isFromExternalAccount() && $usernameFirstPartIsProviderName && $usernameSecondPartIsUser && $userNeverUpdated;
                    },
                ];
            },
            'flash' => [
                SessionFlashKey::CMS_SUCCESS->value => fn () => $request->session()->get(SessionFlashKey::CMS_SUCCESS->value),
                SessionFlashKey::CMS_ERROR->value => fn () => $request->session()->get(SessionFlashKey::CMS_ERROR->value),
                SessionFlashKey::CMS_LOGIN_SUCCESS->value => fn () => $request->session()->get(SessionFlashKey::CMS_LOGIN_SUCCESS->value),
                SessionFlashKey::CMS_EMAIL_VERIFIED->value => fn () => $request->session()->get(SessionFlashKey::CMS_EMAIL_VERIFIED->value),
                SessionFlashKey::CMS_EMAIL_UPDATE_CONFIRMED->value => fn () => $request->session()->get(SessionFlashKey::CMS_EMAIL_UPDATE_CONFIRMED->value),
                SessionFlashKey::PORTFOLIO_SUCCESS->value => fn () => $request->session()->get(SessionFlashKey::PORTFOLIO_SUCCESS->value),
            ],
        ]);
    }
}
