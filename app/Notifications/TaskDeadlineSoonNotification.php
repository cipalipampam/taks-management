<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDeadlineSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected int $hours,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Task deadline soon: '.$this->task->title)
            ->line('A task deadline is approaching.')
            ->line('Title: '.$this->task->title)
            ->line('Deadline: '.$this->task->deadline?->toDateTimeString())
            ->line('Window: '.$this->hours.' hours');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_deadline_soon',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'deadline' => $this->task->deadline?->toDateTimeString(),
            'window_hours' => $this->hours,
        ];
    }
}
