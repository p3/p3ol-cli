<?php

namespace App\DTO;

class Packet
{
    public function __construct(
        public string $data
    ) {
    }

    public static function make(string $data): self
    {
        return new self($data);
    }

    public function hex(): string
    {
        return bin2hex($this->data);
    }

    //@see https://wiki.nina.chat/wiki/Protocols/AOL/Tokens
    public function token(): string
    {
        return hex2binary(substr($this->hex(), 16, 4));
    }
}
