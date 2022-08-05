<?php

namespace App\Parsers;

use App\ValueObjects\Atom as AtomValueObject;
use App\ValueObjects\Packet;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

class Atom
{
    public function __construct(
        private Collection $bytes = new Collection(),
        private Collection $atoms = new Collection(),
    ) {
    }

    public function parseAtoms(Packet $packet): Collection
    {
        collect(['at', 'At', 'AT'])->flatMap(function (string $token) use ($packet): void {
            $this->parseForToken($packet, $token);
        });

        return $this->atoms;
    }

    private function parseForToken(Packet $packet, string $token): void
    {
        $packet->takeToken($token)->each(function (string $hex) use ($token) {
            $this->bytes = str($hex)->substr(20 + $this->sessionIdOffset($token))->split(2);

            while ($this->bytes->count() > 1) {
                $this->atoms->push($this->parseAtom());
            }
        });
    }

    private function sessionIdOffset(string $token): int
    {
        return match ($token) {
            'at' => 8,
            'At' => 6,
            default => 4,
        };
    }

    private function parseAtom(): ?AtomValueObject
    {
        return with($this->bytes->shift(), function (string $byte) {
            return match ($this->atomStyle($byte)) {
                0 => $this->parseFullStyle($byte),
                1 => $this->parseLengthStyle($byte),
                2 => $this->parseDataStyle($byte),
                default => info($byte)
            };
        });
    }

    private function atomStyle(string $byte): int
    {
        return with($this->convertToBinary($byte), function (Stringable $binary): int {
            return bindec($binary->substr(0, 3));
        });
    }

    private function parseFullStyle(string $byte): AtomValueObject
    {
        $protocolId = bindec($this->convertToBinary($byte)->substr(3, 5));
        $atomByte = $this->bytes->shift();
        $atomNumber = hexdec($atomByte);
        $lengthByte = $this->bytes->shift();
        $sizeOfDataLength = $this->convertToBinary($byte)->substr(0, 1);

        $dataLength = (int) $sizeOfDataLength->value === 0
            ? bindec($this->convertToBinary($lengthByte)->substr(1))
            : bindec($this->convertToBinary($lengthByte)->substr(1).$this->convertToBinary($this->bytes->shift()));

        $data = $dataLength > 0 ? $this->bytes->shift($dataLength) : null;

        return $this->makeAtom($protocolId, $atomNumber, $data);
    }

    private function parseLengthStyle(string $byte): AtomValueObject
    {
        $protocolId = bindec($this->convertToBinary($byte)->substr(3, 5));
        $lengthByte = $this->bytes->shift();
        $dataLength = bindec($this->convertToBinary($lengthByte)->substr(0, 3));
        $atomNumber = hexdec($this->convertToBinary($lengthByte)->substr(3, 5));

        $data = $dataLength > 0 ? $this->bytes->shift($dataLength) : null;

        return $this->makeAtom($protocolId, $atomNumber, $data);
    }

    private function parseDataStyle(string $byte): AtomValueObject
    {
        $protocolId = bindec($this->convertToBinary($byte)->substr(3, 5));
        $atomByte = $this->bytes->shift();
        $data = bindec($this->convertToBinary($atomByte)->substr(0, 3));
        $atomNumber = bindec($this->convertToBinary($atomByte)->substr(3, 5));

        return $this->makeAtom($protocolId, $atomNumber, $data);
    }

    private function makeAtom(int $protocolId, int $atomNumber, mixed $data): AtomValueObject
    {
        $data = match (gettype($data)) {
            'string' => $data,
            'object' => $data->join(''),
            default => null,
        };

        return with(AtomName::from($protocolId, $atomNumber), function (string $name) use ($data): AtomValueObject {
            return new AtomValueObject($name, $data, AtomData::parse($name, $data));
        });
    }

    private function convertToBinary(string $byte): Stringable
    {
        return str(base_convert($byte, 16, 2))->padLeft(8, 0);
    }
}
