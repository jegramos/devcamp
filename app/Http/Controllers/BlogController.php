<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class BlogController
{
    public function index(): Response
    {
        return Inertia::render('WorkInProgressPage');
    }
}
