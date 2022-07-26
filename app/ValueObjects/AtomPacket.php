<?php

namespace App\ValueObjects;

use App\Parsers\Atom as AtomParser;
use App\Parsers\AtomData;
use Illuminate\Support\Collection;
use Stringable;

class AtomPacket extends Packet
{
    public function __construct(
        public string $data,
        private Collection $bytes = new Collection(),
        private Collection $atoms = new Collection()
    ) {
    }

    public function atoms(): Collection
    {
        if ($this->atoms->isNotEmpty()) {
            return $this->atoms;
        }

        return with($this->takeToken('AT')->each(fn (string $hex) => $this->mapToAtoms($hex)), function () {
            return $this->atoms;
        });
    }

    public function toFDO(): ?string
    {
        $indent = 0;

        return $this->atoms()->reduce(function (string $carry, Atom $atom, int $index) use (&$indent) {
            $indent = $this->parseIndent($index, $indent);

            $data = ctype_xdigit($atom->data ?? '') && strlen($atom->data) % 2 === 0
                ? str($atom->data)->split(2)->join('x, ').'x'
                : $atom->data;

            return $carry .= str(' ')->repeat($indent * 2)." {$atom->name} <$data>".PHP_EOL;
        }, '');
    }

    private function mapToAtoms(string $hex): void
    {
        $this->bytes = str($hex)->substr(24)->split(2);

        while ($this->bytes->count() > 1) {
            $this->parseAtom();
        }
    }

    private function parseAtom(): void
    {
        with($this->bytes->shift(), function (string $byte) {
            match ($this->atomStyle($byte)) {
                0 => $this->parseFullStyle($byte),
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

    private function parseFullStyle(string $byte)
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

        $this->addAtom($protocolId, $atomNumber, $data);
    }

    private function addAtom(int $protocolId, int $atomNumber, mixed $data): void
    {
        $data = match (gettype($data)) {
            'string' => $data,
            'object' => $data->join(''),
            default => null,
        };

        with(AtomParser::from($protocolId, $atomNumber), function (string $name) use ($data): void {
            $this->atoms->push(new Atom($name, $data, AtomData::parse($name, $data)));
        });
    }

    private function convertToBinary(string $byte): Stringable
    {
        return str(base_convert($byte, 16, 2))->padLeft(8, 0);
    }

    private function parseIndent(int $index, int $indent): int
    {
        if ($this->atoms[$index]->name === 'uni_start_stream') {
            return 0;
        }

        if (str($this->atoms[$index]->name)->is('uni_*end_stream')) {
            return 0;
        }

        if ($index > 0 && str($this->atoms[$index - 1]->name)->is('man_start_object')) {
            return with($indent++, fn () => $indent);
        }

        if ($this->atoms[$index]->name === 'man_end_object') {
            return with($indent--, fn () => $indent);
        }

        return $indent ?: 1;
    }
}
