<?php

namespace App\Actions;

use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;

class IncreasePacketSequence
{
    use AsAction;

    public function handle(Packet $packet): void
    {
        $packet->split()->each(fn ($packet) => $this->increment($packet));
    }

    private function increment(string $packet): void
    {
        cache(['rx_sequence' => hexdec(substr($packet, 10, 2))]);

        with(hexdec(substr($packet, 12, 2)), function (int $tx) {
            $tx === 127 ? cache(['tx_sequence' => 16]) : cache(['tx_sequence' => $tx + 1]);
        });
    }
}
