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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $base_price
 * @property bool $is_eggless
 * @property bool $allows_message
 * @property int $message_max_length
 * @property bool $is_featured
 * @property bool $is_active
 */
#[Fillable([
    'category_id', 'name', 'slug', 'description', 'base_price',
    'is_eggless', 'allows_message', 'message_max_length', 'is_featured', 'is_active',
])]
class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'is_eggless' => 'boolean',
            'allows_message' => 'boolean',
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
     * @return HasMany<ProductWeightOption, $this>
     */
    public function weightOptions(): HasMany
    {
        return $this->hasMany(ProductWeightOption::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<ProductFlavorOption, $this>
     */
    public function flavorOptions(): HasMany
    {
        return $this->hasMany(ProductFlavorOption::class)->orderBy('sort_order');
    }

    /**
     * @return BelongsToMany<Addon, $this>
     */
    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class, 'product_addon');
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
        $this->addMediaCollection('gallery');
    }

    protected function coverImage(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('gallery') ?: null);
    }

    protected function galleryImages(): Attribute
    {
        return Attribute::get(fn () => $this->getMedia('gallery')->map->getUrl()->all());
    }

    protected function defaultWeightOption(): Attribute
    {
        return Attribute::get(function () {
            return $this->weightOptions->firstWhere('is_default', true)
                ?? $this->weightOptions->first();
        });
    }
}
