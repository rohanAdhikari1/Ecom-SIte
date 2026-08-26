<?php

namespace App\Models;

use Database\Factories\ProductOptionTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A configurable axis of choice on a product — e.g. "Size", "Color",
 * "Flavor". Each type owns a set of ProductOptionValue records the
 * customer picks between.
 *
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property int $sort_order
 * @property bool $is_required
 */
#[Fillable(['product_id', 'name', 'sort_order', 'is_required'])]
class ProductOptionType extends Model
{
    /** @use HasFactory<ProductOptionTypeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<ProductOptionValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductOptionValue::class)->orderBy('sort_order');
    }
}
