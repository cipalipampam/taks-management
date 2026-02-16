<?php

namespace App\Notifications;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\Task;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification as BaseNotification;

class TaskStatusChangedNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected string $oldStatus,
        protected string $newStatus,
        protected ?User $actor = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Task status updated: '.$this->task->title)
            ->line('A task status has changed.')
            ->line('Title: '.$this->task->title)
            ->line('From: '.$this->oldStatus)
            ->line('To: '.$this->newStatus);

        if ($this->actor) {
            $message->line('Changed by: '.$this->actor->name);
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'task_status_changed',
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'changed_by_id' => $this->actor?->id,
            'changed_by_name' => $this->actor?->name,
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = "From {$this->oldStatus} to {$this->newStatus}";
        if ($this->actor) {
            $body .= '. Changed by: '.$this->actor->name;
        }

        return Notification::make()
            ->title('Status tugas berubah: '.$this->task->title)
            ->body($body)
            ->actions([
                Action::make('view')
                    ->label(__('View'))
                    ->button()
                    ->url(TaskResource::getUrl('edit', ['record' => $this->task]))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }
}
