<?php

namespace App\Actions;

use Clue\React\Stdio\Stdio;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;

class SendPacket
{
    use AsAction;

    public function handle(Stdio $console, ConnectionInterface $connection, string $packet): void
    {
        if (strlen($packet) % 2 !== 0 || ! ctype_xdigit($packet)) {
            $console->write('Invalid packet.'.PHP_EOL);
        }

        $connection->write(hex2binary($packet));
    }
}
