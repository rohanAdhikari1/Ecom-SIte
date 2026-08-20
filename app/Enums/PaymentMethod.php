<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Cod = 'cod';
    case Esewa = 'esewa';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cod => 'Cash on Delivery',
            self::Esewa => 'eSewa',
        };
    }
}
