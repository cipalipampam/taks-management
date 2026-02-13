<?php

namespace App\Services\Cache;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardCacheService
{
    private const string ADMIN_STATS_KEY = 'dashboard.stats';

    private const int ADMIN_STATS_TTL = 300;

    private const int USER_STATS_TTL = 60;

    /**
     * Get admin dashboard stats (total tasks, by status, users, completion rate).
     * Used by Filament StatsOverview widget.
     *
     * @return array{totalTasks: int, todoTasks: int, doingTasks: int, doneTasks: int, totalUsers: int}
     */
    public static function getAdminStats(): array
    {
        return Cache::remember(self::ADMIN_STATS_KEY, self::ADMIN_STATS_TTL, function () {
            return [
                'totalTasks' => Task::count(),
                'todoTasks' => Task::where('status', 'todo')->count(),
                'doingTasks' => Task::where('status', 'doing')->count(),
                'doneTasks' => Task::where('status', 'done')->count(),
                'totalUsers' => User::count(),
            ];
        });
    }

    /**
     * Forget admin dashboard stats cache.
     */
    public static function forgetAdminStats(): void
    {
        Cache::forget(self::ADMIN_STATS_KEY);
    }

    /**
     * Get user dashboard stats (Not Started, In Progress, Completed).
     * Used by user-dashboard for staff and supervisor.
     *
     * @return array<int, array{label: string, value: int, desc: string, status: string}>
     */
    public static function getUserStats(int $userId): array
    {
        $key = 'user_dashboard_stats_'.$userId;

        return Cache::remember($key, self::USER_STATS_TTL, function () use ($userId) {
            $user = User::find($userId);
            $tasks = $user ? $user->assignedTasks()->get() : collect();

            return [
                ['label' => 'Not Started', 'value' => $tasks->where('status', 'todo')->count(), 'desc' => 'Tasks waiting to be started', 'status' => 'todo'],
                ['label' => 'In Progress', 'value' => $tasks->where('status', 'doing')->count(), 'desc' => 'Tasks currently in progress', 'status' => 'doing'],
                ['label' => 'Completed', 'value' => $tasks->where('status', 'done')->count(), 'desc' => 'Finished tasks', 'status' => 'done'],
            ];
        });
    }

    /**
     * Forget user dashboard stats cache for given user IDs.
     */
    public static function forgetUserStats(array $userIds): void
    {
        foreach (array_unique(array_filter($userIds)) as $uid) {
            Cache::forget('user_dashboard_stats_'.$uid);
        }
    }
}
