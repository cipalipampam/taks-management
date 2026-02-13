<?php

namespace App\Filament\Widgets;

use App\Services\Cache\DashboardCacheService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $stats = DashboardCacheService::getAdminStats();

        return [
            Stat::make('Total Tasks', $stats['totalTasks'])
                ->description('All tasks in the system')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('To Do', $stats['todoTasks'])
                ->description('Pending tasks')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),

            Stat::make('In Progress', $stats['doingTasks'])
                ->description('Tasks being worked on')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('Completed', $stats['doneTasks'])
                ->description('Finished tasks')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Users', $stats['totalUsers'])
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Completion Rate', $stats['totalTasks'] > 0 ? round(($stats['doneTasks'] / $stats['totalTasks']) * 100).'%' : '0%')
                ->description('Tasks completed')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($stats['totalTasks'] > 0 && ($stats['doneTasks'] / $stats['totalTasks']) >= 0.7 ? 'success' : 'warning'),
        ];
    }
}
