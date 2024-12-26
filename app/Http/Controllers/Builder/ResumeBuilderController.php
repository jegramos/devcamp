<?php

namespace App\Http\Controllers\Builder;

use App\Actions\User\UpdateUserAction;
use App\Actions\CreateOrUpdateResumeAction;
use App\Enums\SessionFlashKey;
use App\Enums\SocialNetwork;
use App\Http\Requests\ResumeBuilderRequest;
use App\Models\ResumeTheme;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ResumeBuilderController
{
    public function index(): Response
    {
        /** @var User $user */
        $user = Auth::user()->load('resume');

        return Inertia::render('Builder/Resume/ResumePage', [
            'showGetStarted' => !$user->subdomain,
            'subdomain' => $user->subdomain,
            'availableSocials' => SocialNetwork::toArray(),
            'hasExistingResume' => !empty($user->resume),
            'name' => $user->resume?->name ?? '',
            'titles' => $user->resume?->titles ?? [],
            'experiences' => $user->resume?->experiences ?? [],
            'socials' => $user->resume?->socials ?? [],
            'timeline' => $user->resume?->work_timeline ?? ['history' => [], 'downloadable' => null],
            'services' => $user->resume?->services ?? [],
            'contact' => $user->resume?->contact ?? ['show' => true, 'availability_status' => "Currently available for full-time, part-time, or consultation work."],
            'themeId' => $user->resume?->theme_id ?? null,
            'techExpertise' => $user->resume?->tech_expertise ?? [],
            'projects' => $user->resume?->projects ?? [],
            'themes' => ResumeTheme::query()->get(['id', 'name']),
            'baseSubdomain' => Config::get('app.portfolio_subdomain'),
            'storeSubdomainUrl' => route('builder.resume.storeSubdomain'),
            'storeContentUrl' => route('builder.resume.storeContent'),
            'previewUrl' => $user->subdomain ? $user->subdomain . '.' . Config::get('app.portfolio_subdomain') : null,
            'canPreview' => $user->subdomain && $user->resume,
        ]);
    }

    /**
     * @throws Throwable
     */
    public function storeSubdomain(ResumeBuilderRequest $request, UpdateUserAction $updateUserAction): RedirectResponse
    {
        $updateUserAction->execute(Auth::user(), $request->validated());
        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, "Subdomain saved successfully.");
    }

    public function storeContent(ResumeBuilderRequest $request, CreateOrUpdateResumeAction $createOrUpdateResumeAction): RedirectResponse
    {
        $createOrUpdateResumeAction->execute(Auth::user(), $request->validated());
        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, "Resume content saved successfully.");
    }
}
