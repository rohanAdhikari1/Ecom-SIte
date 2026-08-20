<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Number;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaLibrary extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.media-library';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $title = 'Media Library';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'staff']) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Media::query())
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('preview')
                    ->label('')
                    ->state(fn (Media $record) => $record->getUrl())
                    ->size(64)
                    ->square(),
                TextColumn::make('model_type')
                    ->label('Used on')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->badge()
                    ->color('info'),
                TextColumn::make('collection_name')
                    ->label('Collection')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('file_name')
                    ->label('File')
                    ->searchable()
                    ->limit(35)
                    ->tooltip(fn (Media $record) => $record->file_name),
                TextColumn::make('size')
                    ->label('Size')
                    ->formatStateUsing(fn (int $state) => Number::fileSize($state)),
                TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('j M Y, g:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('model_type')
                    ->label('Used on')
                    ->options(fn () => Media::query()
                        ->distinct()
                        ->pluck('model_type')
                        ->mapWithKeys(fn (string $type) => [$type => class_basename($type)])),
                SelectFilter::make('collection_name')
                    ->label('Collection')
                    ->options(fn () => Media::query()
                        ->distinct()
                        ->pluck('collection_name', 'collection_name')),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Media $record) => $record->getUrl())
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
