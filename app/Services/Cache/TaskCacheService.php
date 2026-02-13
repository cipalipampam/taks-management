<?php

namespace App\Services\Cache;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TaskCacheService
{
    private const int TTL = 60;

    /**
     * Get task list for My Tasks (staff/supervisor - assigned tasks).
     */
    public static function getUserTasksList(int $userId): Collection
    {
        $key = 'user_tasks_list_'.$userId;

        return Cache::remember($key, self::TTL, function () use ($userId) {
            $user = User::find($userId);

            return $user
                ? $user->assignedTasks()->with('creator')->latest()->get()
                : collect();
        });
    }

    /**
     * Get task list for Manage Staff Tasks (supervisor - tasks they created for staff).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getSupervisorTasksList(int $supervisorId): array
    {
        $key = 'supervisor_tasks_list_'.$supervisorId;

        return Cache::remember($key, self::TTL, function () use ($supervisorId) {
            return self::supervisorTasksQuery($supervisorId)
                ->with(['creator', 'assignees'])
                ->latest()
                ->get()
                ->toArray();
        });
    }

    /**
     * Get filtered task IDs for Filament admin (status, assignee, search).
     */
    public static function getFilteredTaskIds(Builder $query, array $filters, int $userId): array
    {
        ksort($filters);
        $cacheKey = 'task_search_'.$userId.'_'.md5(json_encode($filters));

        return self::rememberTaskSearch($cacheKey, self::TTL, function () use ($query, $filters) {
            $q = clone $query;
            if (! empty($filters['status'])) {
                $q->where('status', $filters['status']);
            }
            if (! empty($filters['assignee_id'])) {
                $q->whereHas('assignees', fn (Builder $b) => $b->where('id', $filters['assignee_id']));
            }
            if (! empty($filters['q'])) {
                $q->where('title', 'like', '%'.$filters['q'].'%');
            }

            return $q->pluck('id')->toArray();
        });
    }

    /**
     * Get all task IDs for admin/manager (tasks.manage).
     */
    public static function getAdminTaskListAll(Builder $query): array
    {
        return Cache::remember('tasks_list_all', self::TTL, fn () => $query->pluck('id')->toArray());
    }

    /**
     * Get task IDs for staff/supervisor scope (tasks.manage.staff).
     */
    public static function getAdminTaskListStaffSupervisor(Builder $query): array
    {
        return Cache::remember('tasks_list_staff_supervisor', self::TTL, function () use ($query) {
            return (clone $query)
                ->whereHas('assignees', fn (Builder $b) => $b->whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'supervisor'])))
                ->pluck('id')
                ->toArray();
        });
    }

    /**
     * Forget user-facing task caches (dashboard stats, My Tasks list, Supervisor Tasks list).
     */
    public static function forgetUserFacingTaskCaches(array $userIds): void
    {
        DashboardCacheService::forgetUserStats($userIds);
        foreach (array_unique(array_filter($userIds)) as $uid) {
            Cache::forget('user_tasks_list_'.$uid);
            Cache::forget('supervisor_tasks_list_'.$uid);
        }
    }

    /**
     * Flush all task search filter caches.
     */
    public static function flushTaskSearchCache(): void
    {
        try {
            Cache::tags(['task_search'])->flush();
        } catch (\BadMethodCallException) {
            // Database/file store does not support tags
        }
    }

    /**
     * Remember task search with tag for invalidation (Redis/Memcached).
     */
    public static function rememberTaskSearch(string $key, int $ttl, callable $callback): array
    {
        try {
            return Cache::tags(['task_search'])->remember($key, $ttl, $callback);
        } catch (\BadMethodCallException) {
            return Cache::remember($key, $ttl, $callback);
        }
    }

    /**
     * Forget all task-related caches for a task.
     *
     * @param  array<int>  $assigneeIds  Assignee user IDs (use when relation may be gone, e.g. on delete)
     */
    public static function forgetForTask(Task $task, array $assigneeIds = []): void
    {
        $keys = [
            'tasks_list_all',
            'tasks_list_staff_supervisor',
            "task_detail_{$task->id}",
            "task_{$task->id}_assignees",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        DashboardCacheService::forgetAdminStats();

        $ids = ! empty($assigneeIds)
            ? $assigneeIds
            : $task->assignees()->pluck('users.id')->all();
        $userIds = array_unique(array_merge(
            $ids,
            $task->created_by ? [(int) $task->created_by] : []
        ));

        self::forgetUserFacingTaskCaches($userIds);
        self::flushTaskSearchCache();
    }

    protected static function supervisorTasksQuery(int $supervisorId): Builder
    {
        return Task::query()
            ->where('created_by', $supervisorId)
            ->whereHas(
                'assignees',
                fn (Builder $builder) => $builder->where(function (Builder $query): void {
                    $query
                        ->whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'supervisor']))
                        ->orWhereHas('permissions', fn ($q) => $q->where('name', 'tasks.update-status'))
                        ->orWhereHas('roles.permissions', fn ($q) => $q->where('name', 'tasks.update-status'));
                })
            );
    }
}
