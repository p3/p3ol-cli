<?php

namespace App\ValueObjects;

class Atom
{
    public function __construct(
        public string $name,
        public ?string $hex,
        public ?string $data
    ) {
    }

    public function toBinary(): ?string
    {
        return hex2binary($this->hex);
    }
}
