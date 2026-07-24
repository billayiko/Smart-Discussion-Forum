<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function open(Request $request, string $notification): RedirectResponse
    {
        $notif = $request->user()->notifications()->findOrFail($notification);
        $notif->markAsRead();

        return redirect($notif->data['url'] ?? route($request->user()->dashboardRouteName()));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    /**
     * Dismiss an interrupting popup notification (e.g. an inactivity
     * warning) without navigating away from the page it appeared on.
     */
    public function acknowledge(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()->findOrFail($notification)->markAsRead();

        return back();
    }
}
