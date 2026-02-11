<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDeadlineSoonNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class NotifyTaskDeadlines extends Command
{
    protected $signature = 'tasks:notify-deadline {--hours=24}';

    protected $description = 'Send notifications for tasks with deadlines approaching.';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
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
            }

            $task->deadline_notified_at = now();
            $task->save();
        }

        $this->info('Deadline notifications sent: '.$tasks->count());

        return self::SUCCESS;
    }
}
