<?php

namespace App\Models;

use Database\Factories\DeliveryAreaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $fee
 * @property string|null $eta_text
 * @property bool $is_active
 * @property int $sort_order
 */
#[Fillable(['name', 'fee', 'eta_text', 'is_active', 'sort_order'])]
class DeliveryArea extends Model
{
    /** @use HasFactory<DeliveryAreaFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<DeliveryArea>  $query
     * @return Builder<DeliveryArea>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
