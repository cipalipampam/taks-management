<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $record->load('roles');
        $data['roles'] = $record->roles->pluck('id')->map(fn ($id) => (string) $id)->values()->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();
        $roles = $state['roles'] ?? [];
        $roleIds = array_filter(array_map('intval', (array) $roles));
        $this->record->syncRoles($roleIds);
    }
}
