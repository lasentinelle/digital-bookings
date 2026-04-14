<?php

namespace App;

enum ReservationType: string
{
    case Standard = 'standard';
    case CostOfArtwork = 'cost_of_artwork';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::CostOfArtwork => 'Cost of Artwork',
        };
    }
}
