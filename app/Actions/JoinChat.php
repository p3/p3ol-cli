<?php

namespace App\Actions;

use App\Helpers\Packet;
use App\Enums\ChatPacket;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;

class JoinChat
{
    use AsAction;

    public function handle(ConnectionInterface $connection, string $roomName): void
    {
        with(ChatPacket::cQ_PACKET->value, function ($packet) use ($connection, $roomName) {
            $roomNameLengthByte = str_pad(dechex(strlen($roomName)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{replace}', $roomNameLengthByte.bin2hex($roomName), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $connection->write(Packet::make($packet)->prepare());
        });
    }
}
