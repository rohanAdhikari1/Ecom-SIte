<?php

namespace App\Filament\Resources\Team\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('phone')
                    ->tel(),
                Select::make('role')
                    ->options([
                        'admin' => 'Admin — full access',
                        'staff' => 'Staff — products & orders only',
                    ])
                    ->required()
                    ->native(false),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->label(fn (string $operation) => $operation === 'create' ? 'Password' : 'New password')
                    ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave blank to keep the current password.' : null)
                    ->minLength(8)
                    ->columnSpanFull(),
            ]);
    }
}
