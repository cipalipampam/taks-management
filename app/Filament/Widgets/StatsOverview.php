<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $totalTasks = Task::count();
        $todoTasks = Task::where('status', 'todo')->count();
        $doingTasks = Task::where('status', 'doing')->count();
        $doneTasks = Task::where('status', 'done')->count();
        $totalUsers = User::count();

        return [
            Stat::make('Total Tasks', $totalTasks)
                ->description('All tasks in the system')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('To Do', $todoTasks)
                ->description('Pending tasks')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),

            Stat::make('In Progress', $doingTasks)
                ->description('Tasks being worked on')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),

            Stat::make('Completed', $doneTasks)
                ->description('Finished tasks')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Users', $totalUsers)
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('Completion Rate', $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) . '%' : '0%')
                ->description('Tasks completed')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($totalTasks > 0 && ($doneTasks / $totalTasks) >= 0.7 ? 'success' : 'warning'),
        ];
    }
}
