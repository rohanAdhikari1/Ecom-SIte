<?php

namespace App\Filament\Resources\Team\Pages;

use App\Filament\Resources\Team\TeamResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeamMember extends CreateRecord
{
    protected static string $resource = TeamResource::class;

    protected string $selectedRole = 'staff';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedRole = $data['role'];
        unset($data['role']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles([$this->selectedRole]);
    }
}
