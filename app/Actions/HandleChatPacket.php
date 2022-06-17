<?php

namespace App\Actions;

use App\Actions\SendDesktopNotification;
use App\DTO\Packet;
use App\Enums\AtomPacket;
use Clue\React\Stdio\Stdio;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use function Termwind\{render}; //@codingStandardsIgnoreLine

class HandleChatPacket
{
    use AsAction;
    use WithAttributes;

    public function handle(Stdio $console, Packet $packet): void
    {
        $this->set('console', $console);

        match ($packet->token()) {
            'AT' => $this->parseAtomStream($packet),
            'AB' => $this->parseRoomMessage($packet),
            'CA' => $this->parseEntrance($packet),
            'CB' => $this->parseGoodbye($packet),
            default => info($packet->hex())
        };
    }

    private function parseAtomStream(Packet $packet): void
    {
        match (true) {
            str_contains($packet->hex(), AtomPacket::CHATROOM_LIST->value) => $this->parsePeopleInRoom($packet),
            str_contains($packet->hex(), AtomPacket::INSTANT_MESSAGE->value) => $this->parseInstantMessage($packet),
            default => null
        };
    }

    private function parseInstantMessage(Packet $packet): void
    {
        [$name, $message] = with(str($packet->hex())->after(AtomPacket::INSTANT_MESSAGE->value), function ($data) {
            $name = $data->before('3a2020')->substr(2)->value();
            $message = $data->after('3a2020')->before(AtomPacket::INSTANT_MESSAGE_END->value)->value();

            return [
                hex2binary($name),
                hex2binary(htmlentities($message)),
            ];
        });

        render("\n");
        render(<<<HTML
            <table>
                <thead>
                    <tr>
                        <th>Instant Message From: {$name}</th>
                    </tr>
                    <tbody>
                        <tr>
                            <td>{$message}</td>
                        </tr>
                    </tbody>
                </thead>
            </table>
        HTML);
    }

    private function parsePeopleInRoom(Packet $packet): void
    {
        $roomList = collect(explode('100b01010b0200011d000b01', $packet->hex()))
            ->splice(1)
            ->map(fn ($name) => hex2binary(substr($name, 2, hexdec(substr($name, 0, 2)) * 2)));

        if (! $this->screenName()) {
            Cache::put('screen_name', $roomList->pop());
            $this->console->setPrompt($this->screenName().': ');

            Cache::put('room_list', $roomList);
            $this->console->setAutocomplete(fn () => $roomList->map(fn ($name) => strtolower($name))->toArray());

            $this->console->write($roomList->implode(', ').' are currently in this room.'.PHP_EOL);
        }
    }

    private function parseRoomMessage(Packet $packet): void
    {
        $message = collect(substr($packet->hex(), 20))
            ->flatMap(fn ($data) => str($data)->replaceLast('000000', '|')->explode('|'))
            ->map(fn ($data) => trim(utf8_encode(hex2binary($data))));

        if ($message->first() === $this->screenName()) {
            return;
        }

        if ($this->hasMention($message->last())) {
            SendDesktopNotification::run($message->first(), $message->last());
        }

        $this->console->write($message->join(': ').PHP_EOL);
    }

    private function parseEntrance(Packet $packet): void
    {
        with(hex2binary(substr($packet->hex(), 22, strlen($packet->hex()) - 24)), function ($screenName) {
            Cache::put('room_list', Cache::get('room_list')->push($screenName)->unique());

            $this->console->write($screenName.' has entered the room.'.PHP_EOL);
        });
    }

    private function parseGoodbye(Packet $packet): void
    {
        with(hex2binary(substr($packet->hex(), 22, strlen($packet->hex()) - 24)), function ($screenName) {
            Cache::put('room_list', Cache::get('room_list')->reject(fn ($name) => $name === $screenName));

            $this->console->write($screenName.' has left the room.'.PHP_EOL);
        });
    }

    private function hasMention(string $message): bool
    {
        return preg_match("/\b{$this->screenName()}\b/i", $message);
    }

    private function screenName(): ?string
    {
        return Cache::get('screen_name');
    }
}
