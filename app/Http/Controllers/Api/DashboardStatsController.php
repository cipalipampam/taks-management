<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Cache\DashboardCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardStatsController extends Controller
{
    /**
     * Dashboard stats: admin stats (tasks.manage) or user stats (assigned tasks).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->can('tasks.manage')) {
            $stats = DashboardCacheService::getAdminStats();

            return response()->json([
                'type' => 'admin',
                'stats' => [
                    'total_tasks' => $stats['totalTasks'],
                    'todo_tasks' => $stats['todoTasks'],
                    'doing_tasks' => $stats['doingTasks'],
                    'done_tasks' => $stats['doneTasks'],
                    'total_users' => $stats['totalUsers'],
                    'completion_rate' => $stats['totalTasks'] > 0
                        ? round(($stats['doneTasks'] / $stats['totalTasks']) * 100)
                        : 0,
                ],
            ]);
        }

        $stats = DashboardCacheService::getUserStats($user->id);

        return response()->json([
            'type' => 'user',
            'stats' => $stats,
        ]);
    }
}
