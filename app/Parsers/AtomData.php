<?php

namespace App\Parsers;

use App\Parsers\AtomData\Conditional;
use App\Parsers\AtomData\Man;
use App\Parsers\AtomData\Uni;

class AtomData
{
    public static function parse(string $atomName, ?string $data): ?string
    {
        return match (true) {
            str($atomName)->is('man_*') => Man::parse($atomName, $data),
            str($atomName)->is('uni_*') => Uni::parse($atomName, $data),
            str($atomName)->is('if_*') => Conditional::parse($atomName, $data),
            default => $data
        };
    }
}
