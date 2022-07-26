<?php

namespace App\Parsers\AtomData;

class Conditional
{
    public static function parse(string $atomName, ?string $data): mixed
    {
        return match ($atomName) {
            'if_last_return_true_then' => self::ifLastReturnTrueThen($data),
            default => $data
        };
    }

    public static function ifLastReturnTrueThen(string $data): string
    {
        return str($data)->split(2)->map(fn (string $hex) => hexdec($hex))->implode(', ');
    }
}
