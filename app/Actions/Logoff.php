<?php

namespace App\Actions;

use App\Enums\AuthPacket;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;

class Logoff
{
    use AsAction;

    protected ProgressBar $progressBar;

    public function handle(ConnectionInterface $connection): void
    {
        $connection->write(hex2bin(AuthPacket::pE_PACKET->value));
    }
}
