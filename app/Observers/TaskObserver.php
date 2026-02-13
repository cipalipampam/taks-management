<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskStatusChangedNotification;
use App\Services\Cache\TaskCacheService;
use Illuminate\Support\Facades\Notification;

class TaskObserver
{
    /** @var array<int>|null Captured assignee IDs before delete (relation is cascade-deleted). */
    protected static ?array $deletedTaskAssigneeIds = null;

    public function updated(Task $task): void
    {
        if (! $task->wasChanged('status')) {
            return;
        }

        $assigneeIds = $task->assignees()->pluck('users.id')->all();
        $recipientIds = array_unique(array_merge($assigneeIds, [$task->created_by]));
        $users = User::whereKey($recipientIds)->get();

        if ($users->isEmpty()) {
            return;
        }

        $actor = auth()->user();
        $oldStatus = (string) $task->getOriginal('status');
        $newStatus = (string) $task->status;

        Notification::send($users, new TaskStatusChangedNotification($task, $oldStatus, $newStatus, $actor));
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
