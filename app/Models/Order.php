<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $order_number
 * @property string $tracking_token
 * @property int|null $user_id
 * @property DeliveryMethod $delivery_method
 * @property string $customer_name
 * @property string $phone
 * @property string|null $email
 * @property string $delivery_address
 * @property float|null $delivery_lat
 * @property float|null $delivery_lng
 * @property int|null $delivery_area_id
 * @property Carbon $delivery_date
 * @property string $delivery_time_slot
 * @property PaymentMethod $payment_method
 * @property PaymentStatus $payment_status
 * @property string|null $esewa_transaction_uuid
 * @property string|null $esewa_ref_id
 * @property OrderStatus $status
 * @property int $subtotal
 * @property int $delivery_fee
 * @property int $total
 * @property string|null $notes
 */
#[Fillable([
    'user_id', 'delivery_method', 'customer_name', 'phone', 'email', 'delivery_address', 'delivery_lat', 'delivery_lng',
    'delivery_area_id', 'delivery_date', 'delivery_time_slot', 'payment_method', 'payment_status', 'status',
    'subtotal', 'delivery_fee', 'total', 'notes', 'esewa_transaction_uuid', 'esewa_ref_id',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            $order->tracking_token ??= (string) Str::uuid();
            $order->order_number ??= static::generateOrderNumber();
        });

        static::created(function (Order $order): void {
            $order->statusHistories()->create([
                'status' => $order->status->value,
            ]);
        });
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
    }

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'delivery_lat' => 'float',
            'delivery_lng' => 'float',
            'delivery_method' => DeliveryMethod::class,
            'payment_method' => PaymentMethod::class,
            'payment_status' => PaymentStatus::class,
            'status' => OrderStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<DeliveryArea, $this>
     */
    public function deliveryArea(): BelongsTo
    {
        return $this->belongsTo(DeliveryArea::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderStatusHistory, $this>
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function updateStatus(OrderStatus $status, ?string $note = null): void
    {
        $this->update(['status' => $status]);

        $this->statusHistories()->create([
            'status' => $status->value,
            'note' => $note,
        ]);
    }
}
