<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrdersOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $todayCount = Order::query()->whereDate('created_at', today())->count();
        $pendingCount = Order::query()->where('status', OrderStatus::Pending)->count();
        $weekRevenue = Order::query()
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->where('status', '!=', OrderStatus::Cancelled)
            ->sum('total');

        return [
            Stat::make('Orders Today', (string) $todayCount)
                ->icon('heroicon-o-shopping-bag')
                ->color('primary'),
            Stat::make('Pending Orders', (string) $pendingCount)
                ->icon('heroicon-o-clock')
                ->color('warning'),
            Stat::make('This Week\'s Revenue', 'Rs. '.number_format((float) $weekRevenue))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
