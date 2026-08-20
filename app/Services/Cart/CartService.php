<?php

namespace App\Services\Cart;

use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductFlavorOption;
use App\Models\ProductWeightOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartService
{
    protected const string SESSION_KEY = 'cart';

    /**
     * @param  array<int>  $addonIds
     */
    public function add(
        Product $product,
        ProductWeightOption $weightOption,
        ?ProductFlavorOption $flavorOption,
        array $addonIds,
        ?string $message,
        int $quantity,
    ): void {
        $addons = Addon::query()->active()->whereKey($addonIds)->get();

        $unitPrice = $weightOption->price
            + ($flavorOption?->price_modifier ?? 0)
            + $addons->sum('price');

        $item = [
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'product_image' => $product->cover_image,
            'weight_option_id' => $weightOption->id,
            'weight_label' => $weightOption->label,
            'weight_price' => $weightOption->price,
            'flavor_option_id' => $flavorOption?->id,
            'flavor_label' => $flavorOption?->name,
            'flavor_price_modifier' => $flavorOption?->price_modifier ?? 0,
            'message_on_cake' => $message,
            'addons' => $addons->map(fn (Addon $addon) => [
                'id' => $addon->id,
                'name' => $addon->name,
                'price' => $addon->price,
            ])->values()->all(),
            'quantity' => max(1, $quantity),
            'unit_price' => $unitPrice,
        ];

        $items = $this->rawItems();
        $items[$item['id']] = $item;

        $this->putItems($items);
    }

    public function updateQuantity(string $lineId, int $quantity): void
    {
        $items = $this->rawItems();

        if (! isset($items[$lineId])) {
            return;
        }

        if ($quantity < 1) {
            unset($items[$lineId]);
        } else {
            $items[$lineId]['quantity'] = $quantity;
        }

        $this->putItems($items);
    }

    public function remove(string $lineId): void
    {
        $items = $this->rawItems();
        unset($items[$lineId]);
        $this->putItems($items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function items(): Collection
    {
        return collect($this->rawItems())->map(function (array $item) {
            $item['line_total'] = $item['unit_price'] * $item['quantity'];

            return $item;
        })->values();
    }

    public function count(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function subtotal(): int
    {
        return (int) $this->items()->sum('line_total');
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function rawItems(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    /**
     * @param  array<string, array<string, mixed>>  $items
     */
    protected function putItems(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }
}
