<?php

namespace App\Http\Controllers;

use App\Enums\SessionFlashKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController
{
    public function index(): Response
    {
        $portfolioInquiries = Auth::user()->notifications()
            ->paginate(10)
            ->withQueryString()
            ->through(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at,
                    'read_at' => $notification->read_at,
                    'label' => 'Portfolio Inquiry',
                ];
            });

        return Inertia::render('Misc/NotificationsPage', [
            'portfolioInquiries' => $portfolioInquiries,
            'markAsReadUrl' => route('notifications.markAsRead'),
        ]);
    }

    public function markAsRead(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $id = $request->input('id');

        if (!$id) {
            return redirect()->back()->withErrors([SessionFlashKey::CMS_ERROR->value => 'Invalid notification ID.']);
        }

        $unreadNotification = $user->unreadNotifications()->where('id', $id)->first();
        if ($unreadNotification) {
            $unreadNotification->markAsRead();
        }

        return redirect()->back()->with(SessionFlashKey::CMS_SUCCESS->value, 'Notifications marked as read.');
    }
}
