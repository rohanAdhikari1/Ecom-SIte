<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->weight('semibold'),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->description(fn (Order $record) => $record->phone),
                TextColumn::make('delivery_method')
                    ->label('Fulfilment')
                    ->badge()
                    ->color(fn (DeliveryMethod $state) => $state === DeliveryMethod::Pickup ? 'warning' : 'info'),
                TextColumn::make('deliveryArea.name')
                    ->label('Area')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('delivery_date')
                    ->date('D, j M')
                    ->sortable()
                    ->description(fn (Order $record) => $record->delivery_time_slot),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('payment_method')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('total')
                    ->money('NPR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('delivery_method')
                    ->label('Fulfilment')
                    ->options(DeliveryMethod::class),
                SelectFilter::make('status')
                    ->options(OrderStatus::class),
                SelectFilter::make('payment_status')
                    ->options(PaymentStatus::class),
                SelectFilter::make('payment_method')
                    ->options(PaymentMethod::class),
                SelectFilter::make('delivery_area_id')
                    ->label('Delivery area')
                    ->relationship('deliveryArea', 'name'),
            ])
            ->recordActions([
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
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
