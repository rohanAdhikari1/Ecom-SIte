<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /**
     * @var Collection<string, string|null>|null
     */
    /**
     * @var Collection<string, string|null>|null
     */
    protected static ?Collection $cached = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::allCached()->get($key, $default);
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        static::$cached = null;
    }

    /**
     * @return Collection<string, string|null>
     */
    protected static function allCached(): Collection
    {
        return static::$cached ??= static::query()->pluck('value', 'key');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::$cached = null);
        static::deleted(fn () => static::$cached = null);
    }
}
