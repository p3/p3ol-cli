<?php

namespace App\Actions;

use App\Enums\InstantMessagePacket;
use App\ValueObjects\Packet;
use Clue\React\Stdio\Stdio;
use Codedungeon\PHPCliColors\Color;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;

class SendInstantMessage
{
    use AsAction;

    public function handle(Stdio $console, ConnectionInterface $connection, string $input): void
    {
        [$screenName, $message] = parseArguments($input, 2);

        if (! $screenName || ! $message) {
            return;
        }

        with(InstantMessagePacket::iS_PACKET->value, function ($packet) use ($screenName, $message, $connection) {
            $screenNameLengthByte = str_pad(dechex(strlen($screenName)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{screenName}', $screenNameLengthByte.bin2hex($screenName), $packet);

            $messageLengthByte = str_pad(dechex(strlen($message)), 2, '0', STR_PAD_LEFT);
            $packet = str_replace('{message}', $messageLengthByte.bin2hex($message), $packet);

            $packet = substr_replace($packet, calculatePacketLengthByte($packet), 8, 2);

            $connection->write(Packet::make($packet)->prepare());
        });

        cache(['last_instant_messaged' => $screenName]);
        $console->write(Color::BG_BLUE.'Message has been sent to: '.$screenName.Color::RESET.PHP_EOL);
    }
}
