<?php

namespace App\Actions;

use App\Enums\ChatroomPacket;
use App\Traits\Sound;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;

class SendChatMessage
{
    use AsAction;
    use Sound;

    public function handle(ConnectionInterface $connection, string $message): void
    {
        if (! $message) {
            return;
        }

        with(ChatroomPacket::Aa_PACKET->value, function ($packet) use ($message, $connection) {
            $messageLengthByte = str_pad(dechex(strlen($message)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{replace}', $messageLengthByte.bin2hex($message), $packet);
            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $connection->write(hex2binary($packet));
        });

        $this->playSoundFromText($message);
    }
}
