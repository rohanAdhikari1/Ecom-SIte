<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Needs Action')
                ->icon('heroicon-o-fire')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotIn('status', [
                    OrderStatus::Delivered,
                    OrderStatus::Cancelled,
                ]))
                ->badge(Order::query()->whereNotIn('status', [
                    OrderStatus::Delivered,
                    OrderStatus::Cancelled,
                ])->count()),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::Pending))
                ->badge(Order::query()->where('status', OrderStatus::Pending)->count())
                ->badgeColor('gray'),
            'preparing' => Tab::make('Preparing')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', [
                    OrderStatus::Confirmed,
                    OrderStatus::Preparing,
                ]))
                ->badge(Order::query()->whereIn('status', [
                    OrderStatus::Confirmed,
                    OrderStatus::Preparing,
                ])->count())
                ->badgeColor('warning'),
            'out_for_delivery' => Tab::make('Out for Delivery')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::OutForDelivery))
                ->badge(Order::query()->where('status', OrderStatus::OutForDelivery)->count())
                ->badgeColor('primary'),
            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::Delivered))
                ->badgeColor('success'),
            'all' => Tab::make('All Orders'),
        ];
    }

    public function getDefaultActiveTab(): string
    {
        return 'active';
    }
}
