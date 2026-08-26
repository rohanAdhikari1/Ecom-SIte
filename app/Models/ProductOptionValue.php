<?php

namespace App\Models;

use Database\Factories\ProductOptionValueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One selectable choice within a ProductOptionType — e.g. "2 lb" under a
 * "Size" type, or "Red" under a "Color" type. price_modifier is added to
 * the product's base_price when this value is selected.
 *
 * @property int $id
 * @property int $product_option_type_id
 * @property string $label
 * @property int $price_modifier
 * @property int $sort_order
 * @property bool $is_default
 */
#[Fillable(['product_option_type_id', 'label', 'price_modifier', 'sort_order', 'is_default'])]
class ProductOptionValue extends Model
{
    /** @use HasFactory<ProductOptionValueFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<ProductOptionType, $this>
     */
    public function optionType(): BelongsTo
    {
        return $this->belongsTo(ProductOptionType::class, 'product_option_type_id');
    }
}
