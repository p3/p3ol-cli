<?php

namespace App\Actions;

use App\Enums\AuthPacket;
use App\ValueObjects\Packet;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;

class Logoff
{
    use AsAction;

    public function handle(ConnectionInterface $connection): void
    {
        $connection->write(Packet::make(AuthPacket::pE_PACKET->value)->prepare());

        PlaySound::run('goodbye');
    }
}
