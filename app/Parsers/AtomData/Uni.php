<?php

namespace App\Parsers\AtomData;

use App\Parsers\Atom;
use Illuminate\Support\Collection;

class Uni
{
    public static function parse(string $atomName, ?string $data): mixed
    {
        return match ($atomName) {
            'uni_use_last_atom_value' => self::uniUseLastAtomValue($data),
            default => $data
        };
    }

    public static function uniUseLastAtomValue(string $data): string
    {
        if (strlen($data) !== 4) {
            return $data;
        }

        return with(str($data)->split(2)->map(fn (string $hex) => hexdec($hex)), function (Collection $values) {
            return Atom::from($values[0], $values[1]);
        });
    }
}
