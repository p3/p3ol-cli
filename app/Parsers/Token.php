<?php

namespace App\Parsers;

class Token
{
    public static function from(string $token): ?string
    {
        return match ($token) {
            'AT' => 'AT',
            'Dd' => 'Dd',
            'SC' => 'SC',
            'pE' => 'pE',
            'CJ' => 'CJ',
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
