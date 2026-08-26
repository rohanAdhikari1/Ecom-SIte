<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property string $product_name
 * @property string|null $custom_field_value
 * @property int $quantity
 * @property int $unit_price
 * @property int $addons_total
 * @property int $line_total
 */
#[Fillable([
    'order_id', 'product_id', 'product_name', 'custom_field_value',
    'quantity', 'unit_price', 'addons_total', 'line_total',
])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<OrderItemAddon, $this>
     */
    public function addons(): HasMany
    {
        return $this->hasMany(OrderItemAddon::class);
    }

    /**
     * @return HasMany<OrderItemOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(OrderItemOption::class);
    }
}
