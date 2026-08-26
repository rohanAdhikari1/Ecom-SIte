<?php

namespace App\Models;

use Database\Factories\ProductBadgeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A small reusable label a shop can attach to any product — e.g. "Eggless",
 * "Vegan", "Handmade", "Limited Edition". Fully shop-defined, not tied to
 * any particular product category.
 *
 * @property int $id
 * @property string $name
 * @property string $color
 */
#[Fillable(['name', 'color'])]
class ProductBadge extends Model
{
    /** @use HasFactory<ProductBadgeFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_badge');
    }
}
