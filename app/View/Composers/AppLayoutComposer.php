<?php

namespace App\View\Composers;

use Illuminate\View\View;

class AppLayoutComposer
{
    public function compose(View $view): void
    {
        $unreadNotificationCount = auth()->user()?->unreadNotifications()->count() ?? 0;

        $view->with('unreadNotificationCount', $unreadNotificationCount);
    }
}
