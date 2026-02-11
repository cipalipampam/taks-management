<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected ?User $actor = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Task assigned: '.$this->task->title)
            ->line('A task has been assigned to you.')
            ->line('Title: '.$this->task->title)
            ->line('Status: '.$this->task->status);

        if ($this->task->deadline) {
            $message->line('Deadline: '.$this->task->deadline->toDateTimeString());
        }

        if ($this->actor) {
            $message->line('Assigned by: '.$this->actor->name);
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'status' => $this->task->status,
            'deadline' => $this->task->deadline?->toDateTimeString(),
            'assigned_by_id' => $this->actor?->id,
            'assigned_by_name' => $this->actor?->name,
        ];
    }
}
