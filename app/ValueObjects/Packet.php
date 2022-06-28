<?php

namespace App\ValueObjects;

use App\Enums\PacketToken;
use App\Enums\PacketType;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

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

    public function toStringableHex(): Stringable
    {
        return str($this->toHex());
    }

    public function token(): ?PacketToken
    {
        return PacketToken::fromString(hex2binary(substr($this->toHex(), 16, 4)));
    }

    public function type(): PacketType
    {
        return PacketType::from(hexdec(substr($this->toHex(), 14, 2)));
    }

    public function gid(): ?string
    {
        if ($this->token()?->name !== PacketToken::AT->name) {
            return null;
        }

        return with($this->toStringableHex()->match('/0109(03.*|04.*)/'), function (Stringable $hex) {
            if (! $hex->value) {
                return null;
            }

            $length = hexdec($hex->substr(0, 2)) * 2;

            return collect($hex->substr(2, $length))
                ->flatMap(fn (string $hex) => str_split($hex, 2))
                ->map(fn (string $hex) => hexdec($hex))
                ->when($length === 8, function (Collection $results) {
                    return implode('-', [$results[0], $results[1], (($results[2] * 256) + $results[3])]);
                }, function (Collection $results) {
                    return implode('-', [$results[0], ($results[1] * 256) + $results[2]]);
                });
        });
    }

    public function takeNumber(int $number): self
    {
        if ($number > 0 && ($number - 1) > $this->count()) {
            return $this;
        }

        return new self($this->split()->offsetGet($number - 1));
    }

    public function takeType(PacketType $type): Collection
    {
        return $this->split()
            ->filter(fn ($hex) => PacketType::from(hexdec(substr($hex, 14, 2))) === $type)
            ->map(fn ($hex) => self::make($hex));
    }

    public function takeToken(PacketToken $token): Collection
    {
        return $this->split()
            ->filter(fn ($hex) => PacketToken::fromString(hex2binary(substr($hex, 16, 4))) === $token)
            ->map(fn ($hex) => self::make($hex));
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
