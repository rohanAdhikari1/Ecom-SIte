<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $base_price
 * @property bool $has_custom_field
 * @property string $custom_field_label
 * @property int $custom_field_max_length
 * @property bool $is_featured
 * @property bool $is_active
 */
#[Fillable([
    'category_id', 'name', 'slug', 'description', 'base_price',
    'has_custom_field', 'custom_field_label', 'custom_field_max_length', 'is_featured', 'is_active',
])]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'has_custom_field' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductOptionType, $this>
     */
    public function optionTypes(): HasMany
    {
        return $this->hasMany(ProductOptionType::class)->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<Addon, $this>
     */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'product_addon');
    }

    /**
     * @return BelongsToMany<ProductBadge, $this>
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(ProductBadge::class, 'product_badge');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')->useDisk('public');
    }

    protected function coverImage(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('gallery') ?: null);
    }

    protected function galleryImages(): Attribute
    {
        return Attribute::get(fn () => $this->getMedia('gallery')->map->getUrl()->all());
    }

    /**
     * One default ProductOptionValue per option type — the pre-selected
     * choice shown when a customer first opens the product, keyed by
     * option type id.
     *
     * @return Collection<int, ProductOptionValue>
     */
    protected function defaultOptionSelections(): Attribute
    {
        return Attribute::get(function () {
            return $this->optionTypes->mapWithKeys(function (ProductOptionType $type) {
                $default = $type->values->firstWhere('is_default', true) ?? $type->values->first();

                return [$type->id => $default];
            })->filter();
        });
    }
}
