<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Addon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Product')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Details')
                            ->schema([
                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                TextInput::make('base_price')
                                    ->label('Starting price')
                                    ->helperText('Shown on listing cards; actual price is driven by the weight options below.')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rs.'),
                                Toggle::make('is_eggless')
                                    ->label('Eggless'),
                                Toggle::make('allows_message')
                                    ->label('Allow message on cake')
                                    ->live()
                                    ->default(true),
                                TextInput::make('message_max_length')
                                    ->label('Max message length')
                                    ->numeric()
                                    ->default(40)
                                    ->visible(fn ($get) => $get('allows_message')),
                                Toggle::make('is_featured'),
                                Toggle::make('is_active')
                                    ->default(true),
                            ])
                            ->columns(2),

                        Tab::make('Weight & Pricing')
                            ->schema([
                                Repeater::make('weightOptions')
                                    ->relationship()
                                    ->label('Weight / size options')
                                    ->schema([
                                        TextInput::make('label')
                                            ->placeholder('e.g. 1 lb')
                                            ->required(),
                                        TextInput::make('price')
                                            ->numeric()
                                            ->prefix('Rs.')
                                            ->required(),
                                        TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0),
                                        Toggle::make('is_default'),
                                    ])
                                    ->columns(4)
                                    ->orderColumn('sort_order')
                                    ->addActionLabel('Add weight option')
                                    ->defaultItems(1)
                                    ->reorderable(),
                            ]),

                        Tab::make('Flavors')
                            ->schema([
                                Repeater::make('flavorOptions')
                                    ->relationship()
                                    ->label('Flavor options')
                                    ->schema([
                                        TextInput::make('name')
                                            ->required(),
                                        TextInput::make('price_modifier')
                                            ->label('Extra cost')
                                            ->numeric()
                                            ->default(0)
                                            ->prefix('Rs.'),
                                        TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0),
                                    ])
                                    ->columns(3)
                                    ->orderColumn('sort_order')
                                    ->addActionLabel('Add flavor')
                                    ->reorderable(),
                            ]),

                        Tab::make('Add-ons')
                            ->schema([
                                CheckboxList::make('addons')
                                    ->relationship('addons', 'name')
                                    ->options(fn () => Addon::query()->active()->pluck('name', 'id'))
                                    ->columns(2),
                            ]),

                        Tab::make('Gallery')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('gallery')
                                    ->collection('gallery')
                                    ->multiple()
                                    ->image()
                                    ->imageEditor()
                                    ->reorderable()
                                    ->panelLayout('grid'),
                            ]),
                    ]),
            ]);
    }
}
