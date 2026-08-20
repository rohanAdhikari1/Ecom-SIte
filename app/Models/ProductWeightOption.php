<?php

namespace App\Models;

use Database\Factories\ProductWeightOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property string $label
 * @property int $price
 * @property int $sort_order
 * @property bool $is_default
 */
#[Fillable(['product_id', 'label', 'price', 'sort_order', 'is_default'])]
class ProductWeightOption extends Model
{
    /** @use HasFactory<ProductWeightOptionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
