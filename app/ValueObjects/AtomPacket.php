<?php

namespace App\ValueObjects;

use App\Parsers\Atom as AtomParser;
use Illuminate\Support\Collection;

class AtomPacket extends Packet
{
    public function __construct(
        public string $data,
        private AtomParser $parser = new AtomParser(),
        private Collection $atoms = new Collection()
    ) {
    }

    public function atoms(): Collection
    {
        if ($this->atoms->isNotEmpty()) {
            return $this->atoms;
        }

        return $this->atoms = $this->parser->parseAtoms($this);
    }

    public function toFDO(): ?string
    {
        $indent = 0;

        return $this->atoms()
            ->filter()
            ->reduce(function (string $carry, Atom $atom, int $index) use (&$indent) {
                $indent = $this->parseIndent($index, $indent);

                $data = ctype_xdigit($atom->data ?? '') && strlen($atom->data) % 2 === 0
                    ? str($atom->data)->split(2)->join('x, ').'x'
                    : $atom->data;

                return $carry .= str(' ')->repeat($indent * 2)." {$atom->name} <$data>".PHP_EOL;
            }, '');
    }

    private function parseIndent(int $index, int $indent): int
    {
        if ($this->atoms[$index]->name === 'uni_start_stream') {
            return 0;
        }

        if (str($this->atoms[$index]->name)->is('uni_*end_stream')) {
            return 0;
        }

        if ($index && str($this->atoms[$index - 1]?->name)->is('man_start_object')) {
            return with($indent++, fn () => $indent);
        }

        if ($this->atoms[$index]->name === 'man_end_object') {
            return with($indent--, fn () => $indent);
        }

        return $indent ?: 1;
    }
}
