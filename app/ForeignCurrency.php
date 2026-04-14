<?php

namespace App;

enum ForeignCurrency: string
{
    case EUR = 'EUR';
    case USD = 'USD';

    public function label(): string
    {
        return match ($this) {
            self::EUR => 'EUR',
            self::USD => 'USD',
        };
    }
}
