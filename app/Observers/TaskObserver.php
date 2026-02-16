<?php

namespace App\Observers;

use App\Models\Task;
use App\Services\Cache\TaskCacheService;
use App\Services\Notification\TaskNotificationService;

class TaskObserver
{
    /** @var array<int>|null Captured assignee IDs before delete (relation is cascade-deleted). */
    protected static ?array $deletedTaskAssigneeIds = null;

    public function updated(Task $task): void
    {
        if (! $task->wasChanged('status')) {
            return;
        }

        $oldStatus = (string) $task->getOriginal('status');
        $newStatus = (string) $task->status;

        TaskNotificationService::taskStatusChanged($task, $oldStatus, $newStatus, auth()->user());
    }

    public function created(Task $task): void
    {
        TaskCacheService::forgetForTask($task);
    }

    public function saved(Task $task): void
    {
        TaskCacheService::forgetForTask($task);
    }

    public function deleting(Task $task): void
    {
        self::$deletedTaskAssigneeIds = $task->assignees()->pluck('users.id')->all();
    }

    public function deleted(Task $task): void
    {
        TaskCacheService::forgetForTask($task, self::$deletedTaskAssigneeIds ?? []);
        self::$deletedTaskAssigneeIds = null;
    }

    public static function resetDeletedTaskAssigneeIds(): void
    {
        self::$deletedTaskAssigneeIds = null;
    }
}
