<?php

namespace App\Services\Cart;

use App\Models\Addon;
use App\Models\Product;
use App\Models\ProductOptionValue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartService
{
    protected const string SESSION_KEY = 'cart';

    /**
     * @param  array<int>  $selectedOptionValueIds  one ProductOptionValue id per option type the customer chose
     * @param  array<int>  $addonIds
     */
    public function add(
        Product $product,
        array $selectedOptionValueIds,
        array $addonIds,
        ?string $customFieldValue,
        int $quantity,
    ): void {
        $optionValues = ProductOptionValue::query()
            ->whereKey($selectedOptionValueIds)
            ->whereHas('optionType', fn ($query) => $query->where('product_id', $product->id))
            ->with('optionType')
            ->get();

        abort_unless($optionValues->count() === count($selectedOptionValueIds), 422, 'Invalid option selected for this product.');

        $requiredTypeIds = $product->optionTypes()->where('is_required', true)->pluck('id');
        $selectedTypeIds = $optionValues->pluck('optionType.id');
        abort_unless($requiredTypeIds->diff($selectedTypeIds)->isEmpty(), 422, 'A required option is missing a selection.');

        $addons = $product->addons()->active()->whereKey($addonIds)->get();

        // Add-ons (a candle set, gift wrap, ...) are charged once per line,
        // not per unit — ordering 2 of a product with 1 add-on should charge
        // the add-on once, not twice. Only the product itself (base price +
        // selected option modifiers) scales with quantity.
        $item = [
            'id' => (string) Str::uuid(),
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_slug' => $product->slug,
            'product_image' => $product->cover_image,
            'options' => $optionValues->map(fn (ProductOptionValue $value) => [
                'type_id' => $value->optionType->id,
                'type_name' => $value->optionType->name,
                'value_id' => $value->id,
                'value_label' => $value->label,
                'price_modifier' => $value->price_modifier,
            ])->values()->all(),
            'custom_field_value' => $customFieldValue,
            'addons' => $addons->map(fn (Addon $addon) => [
                'id' => $addon->id,
                'name' => $addon->name,
                'price' => $addon->price,
            ])->values()->all(),
            'quantity' => max(1, $quantity),
            'unit_price' => $product->base_price + (int) $optionValues->sum('price_modifier'),
            'addons_total' => (int) $addons->sum('price'),
        ];

        $items = $this->rawItems();
        $items[$item['id']] = $item;

        $this->putItems($items);
    }

    /**
     * @param  array<int>  $addonIds
     */
    public function updateAddons(string $lineId, array $addonIds): void
    {
        $items = $this->rawItems();

        if (! isset($items[$lineId])) {
            return;
        }

        $product = Product::query()->find((int) $items[$lineId]['product_id']);
        $addons = $product ? $product->addons()->active()->whereKey($addonIds)->get() : collect();

        $items[$lineId]['addons'] = $addons->map(fn (Addon $addon) => [
            'id' => $addon->id,
            'name' => $addon->name,
            'price' => $addon->price,
        ])->values()->all();

        $items[$lineId]['addons_total'] = (int) $addons->sum('price');

        $this->putItems($items);
    }

    public function toggleAddon(string $lineId, int $addonId): void
    {
        $items = $this->rawItems();

        if (! isset($items[$lineId])) {
            return;
        }

        /** @var array<int, array{id: int, name: string, price: int}> $currentAddons */
        $currentAddons = $items[$lineId]['addons'];
        $currentIds = collect($currentAddons)->pluck('id')->all();

        $newIds = in_array($addonId, $currentIds, true)
            ? array_values(array_diff($currentIds, [$addonId]))
            : array_merge($currentIds, [$addonId]);

        $this->updateAddons($lineId, $newIds);
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
        /** @var Collection<int, array<string, mixed>> $items */
        $items = collect($this->rawItems())->map(function (array $item) {
            $item['addons_total'] ??= 0;
            $item['line_total'] = ($item['unit_price'] * $item['quantity']) + $item['addons_total'];

            return $item;
        })->values();

        return $items;
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
