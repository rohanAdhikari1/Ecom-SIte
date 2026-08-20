<?php

namespace App\Support;

class Money
{
    public static function format(int $amount): string
    {
        return 'Rs. '.number_format($amount);
    }
}
