<?php

namespace App\Http\Controllers\Auth;

use App\Actions\User\CreateUserAction;
use App\Actions\User\SyncExternalAccountAction;
use App\Actions\User\UpdateUserAction;
use App\Enums\ErrorCode;
use App\Enums\ExternalLoginProvider;
use App\Enums\Role;
use App\Enums\SessionFlashKey;
use App\Exceptions\DuplicateEmailException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Throwable;

class GithubLoginController
{
    private CreateUserAction $createUserAction;
    private UpdateUserAction $updateUserAction;
    private SyncExternalAccountAction $syncExternalAccountAction;

    public function __construct(CreateUserAction $createUserAction, UpdateUserAction $updateUserAction, SyncExternalAccountAction $syncExternalAccountAction)
    {
        $this->createUserAction = $createUserAction;
        $this->updateUserAction = $updateUserAction;
        $this->syncExternalAccountAction = $syncExternalAccountAction;
    }

    public function redirect(): RedirectResponse
    {
        $config =   Config::get('services.github.oauth');
        $provider = Socialite::buildProvider(GithubProvider::class, $config);
        return $provider->redirect();
    }


    /**
     * @throws Throwable
     */
    public function callback(): RedirectResponse
    {
        $config = Config::get('services.github.oauth');
        $provider = Socialite::buildProvider(GithubProvider::class, $config);

        try {
            $user = $this->syncExternalAccountAction->execute(
                ExternalLoginProvider::GITHUB,
                $provider->user(),
                $this->createUserAction,
                $this->updateUserAction,
                [Role::USER]
            );
        } catch (DuplicateEmailException) {
            $message = 'An account with your Github email address already exists.';
            return redirect(route('auth.login.showForm'))->withErrors([
                ErrorCode::EXTERNAL_ACCOUNT_EMAIL_CONFLICT->value => $message
            ]);
        }

        if (!$user->active) {
            return redirect()->route('auth.login.showForm')->withErrors([
                ErrorCode::ACCOUNT_DEACTIVATED->value => 'Your DevFolio account has been deactivated.',
            ]);
        }

        Auth::login($user);
        Session::regenerate();
        return redirect()
            ->intended(route('builder.resume.index'))
            ->with(SessionFlashKey::CMS_LOGIN_SUCCESS->value, 'You have logged in via Github.');
    }
}
