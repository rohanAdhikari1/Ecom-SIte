<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer & Delivery')
                    ->columns(2)
                    ->components([
                        TextInput::make('order_number')
                            ->disabled()
                            ->dehydrated(false),
                        Select::make('user_id')
                            ->label('Account')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('customer_name')
                            ->required(),
                        TextInput::make('phone')
                            ->tel()
                            ->required(),
                        TextInput::make('email')
                            ->email(),
                        Select::make('delivery_method')
                            ->options(DeliveryMethod::class)
                            ->live()
                            ->required(),
                        Select::make('delivery_area_id')
                            ->label('Delivery area')
                            ->relationship('deliveryArea', 'name')
                            ->visible(fn (Get $get) => $get('delivery_method') !== DeliveryMethod::Pickup->value),
                        Textarea::make('delivery_address')
                            ->required()
                            ->columnSpanFull(),
                        TextEntry::make('delivery_location')
                            ->label('Pinned location')
                            ->columnSpanFull()
                            ->visible(fn (?Order $record) => $record?->delivery_lat && $record?->delivery_lng)
                            ->state(fn (?Order $record) => $record ? new HtmlString(
                                '<a href="https://www.google.com/maps/search/?api=1&query='.$record->delivery_lat.','.$record->delivery_lng.'" target="_blank" rel="noopener" class="text-primary-600 underline">📍 View pinned location on map</a>',
                            ) : null),
                        DatePicker::make('delivery_date')
                            ->required(),
                        TextInput::make('delivery_time_slot')
                            ->required(),
                    ]),

                Section::make('Payment & Status')
                    ->columns(3)
                    ->components([
                        Select::make('payment_method')
                            ->options(PaymentMethod::class)
                            ->required(),
                        Select::make('payment_status')
                            ->options(PaymentStatus::class)
                            ->required(),
                        Select::make('status')
                            ->options(OrderStatus::class)
                            ->required(),
                    ]),

                Section::make('Totals')
                    ->columns(3)
                    ->components([
                        TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('Rs.')
                            ->required(),
                        TextInput::make('delivery_fee')
                            ->numeric()
                            ->prefix('Rs.')
                            ->required(),
                        TextInput::make('total')
                            ->numeric()
                            ->prefix('Rs.')
                            ->required(),
                    ]),

                Grid::make(1)
                    ->components([
                        Textarea::make('notes')
                            ->rows(3),
                    ]),
            ]);
    }
}
