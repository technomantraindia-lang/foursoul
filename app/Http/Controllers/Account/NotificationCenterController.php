<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Phase 8 — Notification Center.
 *
 * Read-only inbox for the authenticated customer's in-app notifications,
 * plus mark-read actions. Ownership is always resolved server-side from
 * the authenticated user — never from request data.
 */
class NotificationCenterController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('channel', NotificationChannel::InApp->value)
            ->latest('created_at')
            ->paginate(20);

        $unreadCount = Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('channel', NotificationChannel::InApp->value)
            ->whereNull('read_at')
            ->count();

        return view('account.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        abort_unless($notification->status !== NotificationStatus::Failed, 404);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->where('channel', NotificationChannel::InApp->value)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}