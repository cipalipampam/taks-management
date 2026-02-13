<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Services\Cache\TaskCacheService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $assigneeIds = $this->record->assignees()->pluck('users.id')->all();

        TaskCacheService::forgetUserFacingTaskCaches($assigneeIds);

        if ($assigneeIds === []) {
            return;
        }

        $users = User::whereKey($assigneeIds)->get();
        if ($users->isNotEmpty()) {
            Notification::send($users, new TaskAssignedNotification($this->record, auth()->user()));
        }

        Log::info('task.assigned', [
            'task_id' => $this->record->id,
            'assignee_ids' => $assigneeIds,
            'assigned_by' => auth()->id(),
            'context' => 'create',
        ]);

        activity()
            ->performedOn($this->record)
            ->causedBy(auth()->user())
            ->withProperties([
                'assignee_ids' => $assigneeIds,
                'context' => 'create',
            ])
            ->log('task.assigned');
    }
}
