<?php

namespace App\Models;

use Database\Factories\OrderItemOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A snapshot of one selected option (type + value + price contribution) on
 * an order line, independent of whether the underlying product/option still
 * exists — mirrors OrderItemAddon's denormalized-snapshot pattern.
 *
 * @property int $id
 * @property int $order_item_id
 * @property string $option_type_name
 * @property string $option_value_label
 * @property int $price_modifier
 */
#[Fillable(['order_item_id', 'option_type_name', 'option_value_label', 'price_modifier'])]
class OrderItemOption extends Model
{
    /** @use HasFactory<OrderItemOptionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
