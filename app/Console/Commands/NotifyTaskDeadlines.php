<?php

namespace App\Console\Commands;

use App\Services\Notification\TaskNotificationService;
use Illuminate\Console\Command;

class NotifyTaskDeadlines extends Command
{
    protected $signature = 'tasks:notify-deadline {--hours=24}';

    protected $description = 'Send notifications for tasks with deadlines approaching.';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $count = TaskNotificationService::sendDeadlineSoonNotifications($hours);

        $this->info('Deadline notifications sent: '.$count);

        return self::SUCCESS;
    }
}
