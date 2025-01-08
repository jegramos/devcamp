<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class CalendarController
{
    public function index(): Response
    {
        return Inertia::render('WorkInProgressPage');
    }
}
