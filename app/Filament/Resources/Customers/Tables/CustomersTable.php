<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (User $record) => CustomerResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (User $record) => $record->email),
                TextColumn::make('phone')
                    ->placeholder('—'),
                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->badge()
                    ->sortable(),
                TextColumn::make('lifetime_value')
                    ->label('Lifetime value')
                    ->money('NPR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('j M Y')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
