<?php

namespace App\Filament\Resources\DeliveryAreas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeliveryAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('fee')
                    ->required()
                    ->numeric()
                    ->prefix('Rs.')
                    ->default(0),
                TextInput::make('eta_text')
                    ->label('Estimated delivery time')
                    ->placeholder('e.g. Same day'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
