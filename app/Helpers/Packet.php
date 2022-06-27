<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

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

    public function prepare(): string
    {
        return $this->sequence()->toBinary();
    }

    public function toHex(): string
    {
        if (ctype_xdigit($this->data) && strlen($this->data) % 2 === 0) {
            return $this->data;
        }

        return bin2hex($this->data);
    }

    public function toBinary(): string
    {
        if (! ctype_xdigit($this->data)) {
            return $this->data;
        }

        return hex2bin($this->data);
    }

    public function token(): string
    {
        return hex2binary(substr($this->toHex(), 16, 4));
    }

    public function split(): Collection
    {
        return with(preg_match_all('/5a(.*?)(0d(?=5a)|0d$)/', $this->toHex(), $matches), function () use ($matches) {
            return collect($matches[0])->filter();
        });
    }

    public function sequence(): self
    {
        $this->data = substr_replace($this->data, dechex($this->tx()).dechex($this->rx()), 10, 4);

        return $this;
    }

    public function incrementSequence(): void
    {
        $this->split()->each(fn ($packet) => $this->increment($packet));
    }

    private function increment(string $packet): void
    {
        cache(['rx_sequence' => hexdec(substr($packet, 10, 2))]);

        with(hexdec(substr($packet, 12, 2)), function (int $tx) {
            $tx === 127 ? cache(['tx_sequence' => 16]) : cache(['tx_sequence' => $tx + 1]);
        });
    }

    private function rx(): int
    {
        return cache('rx_sequence', 127);
    }

    private function tx(): int
    {
        return cache('tx_sequence', 127);
    }
}
