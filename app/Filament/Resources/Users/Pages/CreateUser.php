<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $state = $this->form->getState();
        $roles = $state['roles'] ?? [];
        $roleIds = array_filter(array_map('intval', (array) $roles));
        $this->record->syncRoles($roleIds);

        $permissions = $state['permissions'] ?? [];
        $permissionIds = array_filter(array_map('intval', (array) $permissions));
        $this->record->syncPermissions($permissionIds);
    }
}
