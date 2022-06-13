<?php

namespace App\Actions;

use App\DTO\Packet;
use Clue\React\Stdio\Stdio;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class HandleChatPacket
{
    use AsAction;
    use WithAttributes;

    public function handle(Stdio $console, Packet $packet): void
    {
        $this->set('console', $console);

        match ($packet->token()) {
            'AT' => $this->parsePeopleInRoom($packet),
            'AB' => $this->parseRoomMessage($packet),
            'CA' => $this->parseEnterance($packet),
            'CB' => $this->parseGoodbye($packet),
            default => info($packet->hex())
        };
    }

    // cD packet resposne = info on room
    // cQ is the response to the cQ packet
    private function parsePeopleInRoom(Packet $packet): void
    {
        $roomList = collect(explode('100b01010b0200011d000b01', $packet->hex()))
            ->splice(1)
            ->map(fn ($name) => hex2bin(substr($name, 2, hexdec(substr($name, 0, 2)) * 2)));

        if (! $this->screenName()) {
            Cache::put('screen_name', $roomList->pop());
            $this->console->setPrompt($this->screenName().': ');

            Cache::put('room_list', $roomList);

            $this->console->write($roomList->implode(', ').' are currently in this room.'.PHP_EOL);
        }
    }

    private function parseRoomMessage(Packet $packet): void
    {
        $message = collect(explode('000000', substr($packet->hex(), 20)))
            ->filter()
            ->map(fn ($data) => trim(utf8_encode(hex2bin($data))));

        if ($message->first() === $this->screenName()) {
            return;
        }

        $this->console->write($message->join(': ').PHP_EOL);
    }

    private function parseEnterance(Packet $packet): void
    {
        with(hex2bin(substr($packet->hex(), 22, strlen($packet->hex()) - 24)), function ($screenName) {
            $this->console->write($screenName.' has entered the room.'.PHP_EOL);
        });
    }

    private function parseGoodbye(Packet $packet): void
    {
        with(hex2bin(substr($packet->hex(), 22, strlen($packet->hex()) - 24)), function ($screenName) {
            $this->console->write($screenName.' has left the room.'.PHP_EOL);
        });
    }

    private function screenName(): ?string
    {
        return Cache::get('screen_name');
    }
}
