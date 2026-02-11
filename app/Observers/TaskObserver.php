<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskStatusChangedNotification;
use Illuminate\Support\Facades\Notification;

class TaskObserver
{
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
}
