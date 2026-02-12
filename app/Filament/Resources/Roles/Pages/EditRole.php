<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $record->load('permissions');
        $data['permissions'] = $record->permissions->pluck('id')->map(fn ($id) => (string) $id)->values()->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $state = $this->form->getState();
        $permissions = $state['permissions'] ?? [];
        $permissionIds = array_filter(array_map('intval', (array) $permissions));
        $this->record->syncPermissions($permissionIds);
    }
}
