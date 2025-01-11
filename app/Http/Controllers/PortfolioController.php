<?php

namespace App\Http\Controllers;

use App\Models\ResumeTheme;
use App\Models\User;
use App\Services\CloudStorageManager;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioController
{
    public function index(string $account, CloudStorageManager $cloudStorage): Response
    {
        $user = User::query()->with('resume')->where('subdomain', $account)->first();

        if (!$user || !$user->active) {
            $message = "The account you are looking for does not exist.";
            return Inertia::render('ErrorPage', ['status' => 404, 'message' => $message, 'showActionButtons' => false]);
        }

        if (!$user->resume) {
            $message = "The portfolio information is incomplete. Please fill-out the content of your resume then refresh the page.";
            return Inertia::render('ErrorPage', ['status' => 400, 'message' => $message, 'showActionButtons' => false]);
        }

        $page = $user->resume->theme->page ?? ResumeTheme::default()->page;

        $projectsWithCoverUrl = collect($user->resume->projects)->map(function ($project) use ($cloudStorage) {
            if ($project['cover']) {
                $project['cover'] = $cloudStorage->generateTmpUrl($project['cover'], 60 * 10);
            }

            return $project;
        });

        return Inertia::render("Portfolio/$page", [
            'name' => $user->resume->name,
            'titles' => $user->resume->titles,
            'experiences' => $user->resume->experiences,
            'socials' => $user->resume->socials,
            'techExpertise' => $user->resume->tech_expertise ?? [],
            'projects' => $projectsWithCoverUrl ?? [],
            'timeline' => $user->resume->work_timeline ?? ['history' => [], 'downloadable' => null],
            'services' => $user->resume->services ?? [],
            'contact' => $user->resume->contact,
            'hideNavigation' => empty($user->resume->tech_expertise)
                && empty($user->resume->work_timeline['history'])
                && empty($user->resume->projects)
                && empty($user->resume->services),
            'showNavigation' => [
                'techExpertise' => !empty($user->resume->tech_expertise),
                'workTimeline' => !empty($user->resume->work_timeline['history']),
                'projectHighlights' => !empty($user->resume->projects),
                'services' => !empty($user->resume->services)
            ],
            'sendMessageUrl' => route('portfolio.contact', ['account' => $account]),
        ]);
    }
}
