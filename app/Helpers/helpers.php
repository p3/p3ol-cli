<?php

function calculatePacketLengthByte(string $packet): string
{
    return with(strlen(hex2bin($packet)) - 6, function ($length) use ($packet) {
        return bin2hex(chr($length));
    });
}
