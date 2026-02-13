<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Resources\Tasks\TaskResource;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use App\Services\Cache\TaskCacheService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected array $originalAssigneeIds = [];

    public function mount($record): void
    {
        parent::mount($record);
        $this->originalAssigneeIds = $this->record->assignees()->pluck('users.id')->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $currentAssigneeIds = $this->record->assignees()->pluck('users.id')->all();
        $previousAssigneeIds = $this->originalAssigneeIds;

        TaskCacheService::forgetUserFacingTaskCaches(
            array_unique(array_merge($currentAssigneeIds, $previousAssigneeIds))
        );

        sort($currentAssigneeIds);
        sort($previousAssigneeIds);

        if ($currentAssigneeIds === $previousAssigneeIds) {
            return;
        }

        $addedAssigneeIds = array_values(array_diff($currentAssigneeIds, $previousAssigneeIds));
        if ($addedAssigneeIds !== []) {
            $users = User::whereKey($addedAssigneeIds)->get();
            if ($users->isNotEmpty()) {
                Notification::send($users, new TaskAssignedNotification($this->record, auth()->user()));
            }
        }

        Log::info('task.assigned', [
            'task_id' => $this->record->id,
            'assignee_ids' => $currentAssigneeIds,
            'added' => $addedAssigneeIds,
            'removed' => array_values(array_diff($previousAssigneeIds, $currentAssigneeIds)),
            'assigned_by' => auth()->id(),
            'context' => 'update',
        ]);

        activity()
            ->performedOn($this->record)
            ->causedBy(auth()->user())
            ->withProperties([
                'assignee_ids' => $currentAssigneeIds,
                'added' => $addedAssigneeIds,
                'removed' => array_values(array_diff($previousAssigneeIds, $currentAssigneeIds)),
                'context' => 'update',
            ])
            ->log('task.assigned');
    }
}
