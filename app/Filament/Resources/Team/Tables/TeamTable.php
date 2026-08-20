<?php

namespace App\Filament\Resources\Team\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (User $record) => $record->email),
                TextColumn::make('phone')
                    ->placeholder('—'),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state) => $state === 'admin' ? 'danger' : 'gray')
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date('j M Y')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record) => $record->id !== auth()->id())
                    ->disabled(fn (User $record) => $record->hasRole('admin') && User::role('admin')->count() <= 1)
                    ->tooltip(fn (User $record) => ($record->hasRole('admin') && User::role('admin')->count() <= 1)
                        ? 'At least one admin account must remain'
                        : null),
            ])
            ->toolbarActions([]);
    }
}
