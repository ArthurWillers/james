<?php

namespace App\Http\Controllers;

use Illuminate\Database\Notifications\DatabaseNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Exibe todas as notificações do usuário autenticado.
     */
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Marca uma notificação específica como lida.
     */
    public function markAsRead(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        abort_unless($notification->notifiable_id === $request->user()->id, 403);

        $notification->markAsRead();

        $actionUrl = $notification->data['action_url'] ?? null;

        return $actionUrl
            ? redirect($actionUrl)
            : back();
    }

    /**
     * Marca todas as notificações do usuário como lidas.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
