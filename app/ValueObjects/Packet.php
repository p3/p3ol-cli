<?php

namespace App\ValueObjects;

use App\Enums\PacketType;
use App\Parsers\Token;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

class Packet
{
    public function __construct(
        public string $data
    ) {
    }

    public static function make(string $data): mixed
    {
        return match (true) {
            self::isAtomStream($data) => new AtomPacket($data),
            default => new self($data),
        };
    }

    public static function isAtomStream(string $data): bool
    {
        $data = ctype_xdigit($data) && strlen($data) % 2 === 0 ? $data : bin2hex($data);

        return in_array(hex2binary(substr($data, 16, 4)), ['AT', 'At', 'at']);
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

    public function toStringableHex(): Stringable
    {
        return str($this->toHex());
    }

    public function token(): ?string
    {
        return Token::from(hex2binary(substr($this->toHex(), 16, 4)));
    }

    public function type(): PacketType
    {
        return PacketType::from(hexdec(substr($this->toHex(), 14, 2)) & hexdec('7F'));
    }

    public function takeNumber(int $number): self
    {
        if ($number > 0 && ($number - 1) > $this->count()) {
            return $this;
        }

        return new static($this->split()->offsetGet($number - 1));
    }

    public function takeType(PacketType $type): Collection
    {
        return $this->split()->filter(fn ($hex) => PacketType::from(hexdec(substr($hex, 14, 2))) === $type);
    }

    public function takeToken(string $token): Collection
    {
        return $this->split()->filter(fn ($hex) => hex2binary(substr($hex, 16, 4)) === $token);
    }

    public function count(): int
    {
        return $this->split()->count();
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

    private function rx(): int
    {
        return cache('rx_sequence', 127);
    }

    private function tx(): int
    {
        return cache('tx_sequence', 127);
    }
}
