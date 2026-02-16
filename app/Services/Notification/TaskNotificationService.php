<?php

namespace App\Services\Notification;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskDeadlineSoonNotification;
use App\Notifications\TaskStatusChangedNotification;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Notification;

class TaskNotificationService
{
    /**
     * Send task assigned notifications to the given assignee user IDs.
     */
    public static function taskAssigned(Task $task, array $assigneeIds, ?User $actor = null): void
    {
        if ($assigneeIds === []) {
            return;
        }

        $users = User::whereKey($assigneeIds)->get();
        if ($users->isNotEmpty()) {
            Notification::send($users, new TaskAssignedNotification($task, $actor));

            $panelRecipients = $users->filter->isAdmin();
            foreach ($panelRecipients as $recipient) {
                FilamentNotification::make()
                    ->title('Task assigned')
                    ->body($task->title)
                    ->icon('heroicon-o-clipboard-document-check')
                    ->sendToDatabase($recipient);
            }
        }
    }

    /**
     * Send task status changed notifications to assignees and creator.
     */
    public static function taskStatusChanged(Task $task, string $oldStatus, string $newStatus, ?User $actor = null): void
    {
        $assigneeIds = $task->assignees()->pluck('users.id')->all();
        $recipientIds = array_unique(array_merge($assigneeIds, [$task->created_by]));
        $users = User::whereKey($recipientIds)->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new TaskStatusChangedNotification($task, $oldStatus, $newStatus, $actor));

        $panelRecipients = $users->filter->isAdmin();
        foreach ($panelRecipients as $recipient) {
            FilamentNotification::make()
                ->title('Task status updated')
                ->body("{$task->title}: {$oldStatus} → {$newStatus}")
                ->icon('heroicon-o-arrow-path')
                ->sendToDatabase($recipient);
        }
    }

    /**
     * Send deadline soon notifications for tasks with deadline in the given hours window.
     * Returns the number of tasks for which notifications were sent.
     */
    public static function sendDeadlineSoonNotifications(int $hours = 24): int
    {
        if ($hours <= 0) {
            $hours = 24;
        }

        $start = now();
        $end = now()->addHours($hours);

        $tasks = Task::query()
            ->whereNotNull('deadline')
            ->whereNull('deadline_notified_at')
            ->whereBetween('deadline', [$start, $end])
            ->where('status', '!=', 'done')
            ->with('assignees')
            ->get();

        foreach ($tasks as $task) {
            $assigneeIds = $task->assignees->pluck('id')->all();
            $recipientIds = array_unique(array_merge($assigneeIds, [$task->created_by]));
            $users = User::whereKey($recipientIds)->get();

            if ($users->isNotEmpty()) {
                Notification::send($users, new TaskDeadlineSoonNotification($task, $hours));

                $panelRecipients = $users->filter->isAdmin();
                foreach ($panelRecipients as $recipient) {
                    FilamentNotification::make()
                        ->title('Task deadline soon')
                        ->body($task->title)
                        ->icon('heroicon-o-clock')
                        ->sendToDatabase($recipient);
                }
            }

            $task->deadline_notified_at = now();
            $task->save();
        }

        return $tasks->count();
    }
}
