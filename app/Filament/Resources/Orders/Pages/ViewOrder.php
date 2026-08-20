<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpan(3)
                    ->columns(4)
                    ->components([
                        TextEntry::make('order_number')
                            ->label('Order #')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('payment_status')
                            ->badge(),
                        TextEntry::make('total')
                            ->money('NPR')
                            ->weight('bold'),
                    ]),

                Section::make('Customer')
                    ->columnSpan(1)
                    ->components([
                        TextEntry::make('customer_name')
                            ->label('Name'),
                        TextEntry::make('phone')
                            ->icon('heroicon-o-phone')
                            ->copyable(),
                        TextEntry::make('email')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('—'),
                        TextEntry::make('user.name')
                            ->label('Account')
                            ->placeholder('Guest checkout'),
                    ]),

                Section::make('Fulfilment')
                    ->columnSpan(2)
                    ->columns(2)
                    ->components([
                        TextEntry::make('delivery_method')
                            ->badge(),
                        TextEntry::make('deliveryArea.name')
                            ->label('Area')
                            ->placeholder('—'),
                        TextEntry::make('delivery_date')
                            ->date('D, j M Y'),
                        TextEntry::make('delivery_time_slot')
                            ->label('Time slot'),
                        TextEntry::make('delivery_address')
                            ->label('Address')
                            ->columnSpanFull(),
                        ViewEntry::make('map')
                            ->label('Pinned location')
                            ->view('filament.infolists.order-map')
                            ->columnSpanFull(),
                    ]),

                Section::make('Items')
                    ->columnSpan(3)
                    ->components([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(6)
                                    ->components([
                                        TextEntry::make('product_name')
                                            ->label('Product')
                                            ->weight('semibold')
                                            ->columnSpan(2),
                                        TextEntry::make('weight_label')
                                            ->label('Weight'),
                                        TextEntry::make('flavor_label')
                                            ->label('Flavor')
                                            ->placeholder('—'),
                                        TextEntry::make('quantity')
                                            ->label('Qty'),
                                        TextEntry::make('line_total')
                                            ->label('Total')
                                            ->money('NPR'),
                                        TextEntry::make('message_on_cake')
                                            ->label('Message on cake')
                                            ->placeholder('—')
                                            ->columnSpanFull(),
                                        TextEntry::make('addons')
                                            ->label('Add-ons')
                                            ->placeholder('—')
                                            ->state(fn ($record) => $record->addons->pluck('addon_name')->implode(', ') ?: null)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),

                Section::make('Status History')
                    ->columnSpan(3)
                    ->components([
                        RepeatableEntry::make('statusHistories')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->components([
                                        TextEntry::make('status')
                                            ->badge(),
                                        TextEntry::make('created_at')
                                            ->dateTime('j M Y, g:i A'),
                                        TextEntry::make('note')
                                            ->placeholder('—')
                                            ->columnSpan(2),
                                    ]),
                            ]),
                    ]),

                Section::make('Notes')
                    ->columnSpan(3)
                    ->visible(fn (Order $record) => filled($record->notes))
                    ->components([
                        TextEntry::make('notes')
                            ->label(''),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('advance')
                ->label(fn (Order $record) => 'Mark '.$record->status->next()?->getLabel())
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (Order $record) => $record->status->next() !== null)
                ->action(function (Order $record): void {
                    $next = $record->status->next();
                    $record->updateStatus($next);

                    Notification::make()
                        ->title('Order marked as '.$next->getLabel())
                        ->success()
                        ->send();
                }),
            Action::make('changeStatus')
                ->label('Change status')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->schema([
                    Select::make('status')
                        ->options(OrderStatus::class)
                        ->required(),
                    Textarea::make('note')
                        ->label('Note (optional)'),
                ])
                ->fillForm(fn (Order $record) => ['status' => $record->status->value])
                ->action(function (Order $record, array $data): void {
                    $record->updateStatus($data['status'], $data['note'] ?: null);

                    Notification::make()
                        ->title('Order status updated')
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
