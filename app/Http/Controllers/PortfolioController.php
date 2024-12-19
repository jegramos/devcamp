<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioController
{
    public function index(string $account): Response
    {
        $accountFound = User::where('subdomain', $account)->exists();

        if (!$accountFound) {
            $message = "The account you're looking for doesn't exist.";
            return Inertia::render('ErrorPage', ['status' => 404, 'message' => $message]);
        }

        return Inertia::render('Portfolio/GradientThemePage');
    }
}
