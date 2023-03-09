<?php

namespace App\Parsers;

class Token
{
    public static function from(string $token): ?string
    {
        return match ($token) {
            'at' => 'at',
            'At' => 'At',
            'AT' => 'AT',
            'AA' => 'AA',
            'Dd' => 'Dd',
            'SC' => 'SC',
            'pE' => 'pE',
            'LB' => 'LB',
            'cQ' => 'cQ',
            'CA' => 'CA',
            'CB' => 'CB',
            'Aa' => 'Aa',
            'AB' => 'AB',
            'iS' => 'iS',
            'ji' => 'ji',
            'uD' => 'uD',
            default => null
        };
    }
}
