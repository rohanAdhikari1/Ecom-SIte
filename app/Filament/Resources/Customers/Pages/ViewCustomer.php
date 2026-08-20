<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpan(3)
                    ->columns(4)
                    ->components([
                        TextEntry::make('name')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->icon('heroicon-o-phone')
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('created_at')
                            ->label('Customer since')
                            ->date('j M Y'),
                    ]),

                Section::make('Lifetime Value')
                    ->columnSpan(1)
                    ->columns(2)
                    ->components([
                        TextEntry::make('orders_count')
                            ->label('Total orders'),
                        TextEntry::make('lifetime_value')
                            ->label('Delivered total')
                            ->money('NPR'),
                    ]),

                Section::make('Saved Addresses')
                    ->columnSpan(2)
                    ->components([
                        RepeatableEntry::make('addresses')
                            ->label('')
                            ->schema([
                                Grid::make(3)
                                    ->components([
                                        TextEntry::make('label')
                                            ->weight('semibold'),
                                        TextEntry::make('address')
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->columnSpanFull()
                            ->placeholder('No saved addresses'),
                    ]),

                Section::make('Recent Orders')
                    ->columnSpan(3)
                    ->components([
                        RepeatableEntry::make('orders')
                            ->label('')
                            ->state(fn ($record) => $record->orders()->latest()->take(10)->get())
                            ->schema([
                                Grid::make(5)
                                    ->components([
                                        TextEntry::make('order_number')
                                            ->label('Order #')
                                            ->weight('semibold')
                                            ->url(fn ($record) => OrderResource::getUrl('view', ['record' => $record])),
                                        TextEntry::make('status')
                                            ->badge(),
                                        TextEntry::make('payment_status')
                                            ->badge(),
                                        TextEntry::make('total')
                                            ->money('NPR'),
                                        TextEntry::make('created_at')
                                            ->dateTime('j M Y'),
                                    ]),
                            ])
                            ->placeholder('No orders yet'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
