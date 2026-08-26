<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Addon;
use App\Models\ProductBadge;
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
                                    ->helperText('Shown on listing cards; actual price is base price + any selected option modifiers below.')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rs.'),
                                CheckboxList::make('badges')
                                    ->relationship('badges', 'name')
                                    ->options(fn () => ProductBadge::query()->pluck('name', 'id'))
                                    ->columns(2)
                                    ->helperText('Manage the available badges under Settings → Product Badges.'),
                                Toggle::make('has_custom_field')
                                    ->label('Allow a custom text field on this product')
                                    ->helperText('E.g. "Message on Cake" for a bakery, "Engraving Text" for a jeweler.')
                                    ->live()
                                    ->default(true),
                                TextInput::make('custom_field_label')
                                    ->label('Field label')
                                    ->default('Special Instructions')
                                    ->visible(fn ($get) => $get('has_custom_field')),
                                TextInput::make('custom_field_max_length')
                                    ->label('Max length')
                                    ->numeric()
                                    ->default(40)
                                    ->visible(fn ($get) => $get('has_custom_field')),
                                Toggle::make('is_featured'),
                                Toggle::make('is_active')
                                    ->default(true),
                            ])
                            ->columns(2),

                        Tab::make('Options')
                            ->schema([
                                Repeater::make('optionTypes')
                                    ->relationship()
                                    ->label('Option types')
                                    ->helperText('Each type is one axis of choice — e.g. "Size", "Color", "Material". A customer picks one value per type.')
                                    ->schema([
                                        TextInput::make('name')
                                            ->placeholder('e.g. Size')
                                            ->required(),
                                        Toggle::make('is_required')
                                            ->label('Customer must choose a value'),
                                        TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0),
                                        Repeater::make('values')
                                            ->relationship()
                                            ->label('Values')
                                            ->schema([
                                                TextInput::make('label')
                                                    ->placeholder('e.g. 2 lb')
                                                    ->required(),
                                                TextInput::make('price_modifier')
                                                    ->label('Price change')
                                                    ->helperText('Added to the base price when selected. Can be 0 or negative.')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->prefix('Rs.'),
                                                TextInput::make('sort_order')
                                                    ->numeric()
                                                    ->default(0),
                                                Toggle::make('is_default'),
                                            ])
                                            ->columns(4)
                                            ->orderColumn('sort_order')
                                            ->addActionLabel('Add value')
                                            ->defaultItems(1)
                                            ->reorderable()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(3)
                                    ->orderColumn('sort_order')
                                    ->addActionLabel('Add option type')
                                    ->reorderable()
                                    ->collapsible(),
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
