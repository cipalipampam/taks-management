<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AdminNotificationsWidget extends Widget
{
    protected string $view = 'filament.widgets.admin-notifications-widget';

    protected static ?int $sort = -1; // Show at the top

    public function getNotifications()
    {
        return Auth::user()
            ->notifications()
            ->latest()
            ->take(10)
            ->get();
    }
}
