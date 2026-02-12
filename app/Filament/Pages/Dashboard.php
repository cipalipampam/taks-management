<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\StatsOverview;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            // Notifications disabled for now
            // \App\Filament\Widgets\AdminNotificationsWidget::class,
            StatsOverview::class,
        ];
    }
}
