<?php

namespace App\Actions;

use App\Actions\PlaySound;
use App\Enums\AuthPacket;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;

class Logoff
{
    use AsAction;

    public function handle(ConnectionInterface $connection): void
    {
        $connection->write(hex2binary(AuthPacket::pE_PACKET->value));

        PlaySound::run('goodbye');
    }
}
