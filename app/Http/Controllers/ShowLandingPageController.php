<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowLandingPageController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('LandingPage', [
            'loginUrl' => route('auth.login.showForm'),
            'registerUrl' => route('auth.register.showForm'),
        ]);
    }
}
