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
 * @property string $weight_label
 * @property int $weight_price
 * @property string|null $flavor_label
 * @property int $flavor_price_modifier
 * @property string|null $message_on_cake
 * @property int $quantity
 * @property int $unit_price
 * @property int $line_total
 */
#[Fillable([
    'order_id', 'product_id', 'product_name', 'weight_label', 'weight_price',
    'flavor_label', 'flavor_price_modifier', 'message_on_cake', 'quantity', 'unit_price', 'line_total',
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
}
