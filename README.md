# ShopKit

An open-source, vertical-agnostic ecommerce storefront and admin panel built with Laravel, Livewire, and Filament.

ShopKit ships with a fully generic product/variant model — products define their own **option types** (e.g. "Size", "Color", "Material") and each type offers a set of **option values** that modify the base price. There's no hardcoded notion of "weight" or "flavor": a bakery, a print shop, and a clothing store can all run on the same schema. A working demo catalog (a cake shop) is seeded out of the box so you can see the whole flow — browsing, cart, checkout, and order tracking — working end to end.

## Features

- **Generic product configurator** — admin-defined option types/values with per-value price modifiers, required/optional axes, and a live-updating storefront configurator.
- **Product add-ons** — optional extras attached to a product, charged once per cart line regardless of quantity.
- **Custom text field per product** — an admin-labelled free-text field on checkout (e.g. a gift note, an engraving, a special instruction).
- **Product badges** — admin-defined tags (Eggless, Vegan, Handmade, Limited Edition, ...) shown on product cards and detail pages.
- **Cart & checkout** — session-based cart, delivery-area based shipping fees, self-pickup, guest and account checkout.
- **Payments** — Cash on Delivery, and a full eSewa (Nepali payment gateway) integration with signed callbacks.
- **Order tracking** — public order lookup by order number + phone, live status polling, full status history.
- **Admin panel** (Filament) — products with nested option-type/option-value repeaters, orders with map-based delivery view, categories, add-ons, delivery areas, banners, testimonials, and site settings — all editable without touching code.
- **Media library** — Spatie Media Library-backed image management for products and banners, with a Filament-native upload/preview UI.
- **Role-based access** — Spatie Permission-backed admin/staff roles.

## Tech Stack

- [Laravel 13](https://laravel.com) (PHP 8.3+)
- [Livewire 4](https://livewire.laravel.com) — single-file components (`resources/views/pages/⚡*.blade.php`)
- [Filament 5](https://filamentphp.com) — the admin panel at `/crm`
- [Tailwind CSS v4](https://tailwindcss.com) — theme colors are two hex anchors in `resources/css/app.css`, see [Re-theming](#re-theming) below
- [Pest](https://pestphp.com) — the test suite

## Getting Started

```bash
git clone <this-repo>
cd shopkit
composer run setup
composer run dev
```

`composer run setup` copies `.env.example` to `.env`, generates an app key, runs migrations, and installs/builds frontend assets. `composer run dev` runs the app server, queue worker, and Vite dev server together.

Then seed the demo catalog and an admin account:

```bash
php artisan db:seed
```

This creates an admin user at `admin@example.com` / `password` — change this password before deploying anywhere public. Log in to the admin panel at `/crm`.

### Configuration

Site-wide settings (shop name, contact details, delivery fee, hero copy, eSewa toggle, ...) are editable from **Settings** inside the admin panel — no code changes needed to re-skin the storefront's content for a real shop.

To enable eSewa payments, set the following in `.env` (see [eSewa's developer docs](https://developer.esewa.com.np/pages/Epay)):

```
ESEWA_PRODUCT_CODE=
ESEWA_SECRET_KEY=
ESEWA_FORM_URL=
ESEWA_STATUS_URL=
```

## Re-theming

The whole color system derives from two hex anchors in `resources/css/app.css`:

```css
--color-brand-base: #4f46e5;   /* primary */
--color-accent-base: #f59e0b;  /* accent */
```

Every shade (`brand-50` through `brand-950`, `accent-50` through `accent-900`) is generated from these two values with `color-mix()`. Change the anchors to re-skin the entire storefront and admin panel.

## Demo Content

The seeded catalog, banners, and testimonials are sample bakery content meant to demonstrate the generic option-type/option-value model — swap them out for your own products, categories, and images via the admin panel or by editing the seeders in `database/seeders/`.

## Testing

```bash
composer run test
```

Runs Pint (code style), PHPStan (static analysis), and the full Pest suite.

## License

Released under the [MIT License](LICENSE).
