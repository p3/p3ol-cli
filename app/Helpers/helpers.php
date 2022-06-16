<?php

function calculatePacketLengthByte(string $packet): string
{
    return with(strlen(hex2bin($packet)) - 6, function ($length) use ($packet) {
        return bin2hex(chr($length));
    });
}

function hex2binary(string $string): ?string
{
    if (ctype_xdigit($string) && strlen($string) % 2 == 0) {
        return hex2bin($string);
    }

    info('Invalid hex parsed: ' . $string);

    return null;
}