<?php

namespace App\Actions;

use App\Enums\ChatroomPacket;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;

class JoinChat
{
    use AsAction;

    public function handle(ConnectionInterface $connection, string $roomName): void
    {
        with(ChatroomPacket::cQ_PACKET->value, function ($packet) use ($connection, $roomName) {
            $roomNameLengthByte = str_pad(dechex(strlen($roomName)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{replace}', $roomNameLengthByte.bin2hex($roomName), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $connection->write(hex2binary($packet));
        });
    }
}
