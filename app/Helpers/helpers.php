<?php

function calculatePacketLengthByte(string $packet): string
{
    return with(strlen(hex2bin($packet)) - 6, function ($length) {
        return bin2hex(chr($length));
    });
}

function parseArguments(string $input, int $count = 2): array
{
    return with(explode(' ', $input, $count), function (array $results) use ($count) {
        return [
            ...$results,
            ...collect()->times($count - count($results))->map(fn () => null)->toArray(),
        ];
    });
}

function hex2binary(string $string): ?string
{
    if (ctype_xdigit($string) && strlen($string) % 2 == 0) {
        return hex2bin($string);
    }

    info('Invalid hex parsed: '.$string);

    return null;
}
