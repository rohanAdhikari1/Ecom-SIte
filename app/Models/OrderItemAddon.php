<?php

namespace App\Models;

use Database\Factories\OrderItemAddonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_item_id
 * @property string $addon_name
 * @property int $addon_price
 */
#[Fillable(['order_item_id', 'addon_name', 'addon_price'])]
class OrderItemAddon extends Model
{
    /** @use HasFactory<OrderItemAddonFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
