<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('subtitle')
                    ->columnSpanFull(),
                TextInput::make('cta_label')
                    ->label('Button label'),
                TextInput::make('cta_url')
                    ->label('Button link'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                SpatieMediaLibraryFileUpload::make('image')
                    ->collection('image')
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
