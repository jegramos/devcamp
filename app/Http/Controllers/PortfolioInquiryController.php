<?php

namespace App\Http\Controllers;

use App\Enums\SessionFlashKey;
use App\Http\Requests\PortfolioInquiryRequest;
use App\Models\User;
use App\Notifications\PortfolioInquiryNotification;
use Illuminate\Http\RedirectResponse;

class PortfolioInquiryController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $account, PortfolioInquiryRequest $request): RedirectResponse
    {
        $user = User::query()->with('resume')->where('subdomain', $account)->firstOrFail();

        $name = $request->input('name');
        $email = $request->input('email');
        $message = $request->input('message');

        $user->notify(new PortfolioInquiryNotification($name, $email, $message));
        return redirect()->back()->with(SessionFlashKey::PORTFOLIO_SUCCESS->value, 'Your message has been sent.');
    }
}
