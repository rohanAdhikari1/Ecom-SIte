<?php

namespace App\Models;

use Database\Factories\ProductFlavorOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property int $price_modifier
 * @property int $sort_order
 */
#[Fillable(['product_id', 'name', 'price_modifier', 'sort_order'])]
class ProductFlavorOption extends Model
{
    /** @use HasFactory<ProductFlavorOptionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
