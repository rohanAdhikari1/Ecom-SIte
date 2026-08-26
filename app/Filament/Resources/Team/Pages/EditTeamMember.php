<?php

namespace App\Filament\Resources\Team\Pages;

use App\Filament\Resources\Team\TeamResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditTeamMember extends EditRecord
{
    protected static string $resource = TeamResource::class;

    protected ?string $selectedRole = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var User $user */
        $user = $this->record;

        /** @var Role|null $role */
        $role = $user->roles->first();

        $data['role'] = $role?->name;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->selectedRole = $data['role'];
        unset($data['role']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var User $user */
        $user = $this->record;

        $user->syncRoles([$this->selectedRole]);
    }
}
