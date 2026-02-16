<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Services\Cache\TaskCacheService;
use App\Services\Notification\TaskNotificationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

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

        TaskNotificationService::taskAssigned($this->record, $assigneeIds, auth()->user());

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
