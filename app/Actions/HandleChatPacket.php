<?php

namespace App\Actions;

use App\DTO\Packet;
use App\Enums\AtomPacket;
use App\Traits\Sound;
use AsciiTable\Builder;
use Clue\React\Stdio\Stdio;
use Codedungeon\PHPCliColors\Color;
use Illuminate\Support\Stringable;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class HandleChatPacket
{
    use AsAction;
    use WithAttributes;
    use Sound;

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
        [$screenName, $message] = str($packet->hex())
            ->after(AtomPacket::INSTANT_MESSAGE->value)
            ->before(AtomPacket::INSTANT_MESSAGE_END->value)
            ->substr(2)
            ->replace('3a2020', '|')
            ->explode('|')
            ->map(fn (string $data) => hex2binary($data));

        with(new Builder, function (Builder $builder) use ($screenName, $message) {
            $builder->setTitle('New Instant Message 💌');
            $builder->addRow([
                'Screenname' => $screenName,
                'Message' =>  $message,
            ]);

            $this->console->write($builder->renderTable().PHP_EOL);
            $this->console->write(Color::BG_BLUE.'Press the down arrow to reply'.Color::RESET.PHP_EOL);
            PlaySound::run('im');
        });
    }

    private function parsePeopleInRoom(Packet $packet): void
    {
        $roomList = collect(explode('100b01010b0200011d000b01', $packet->hex()))
            ->splice(1)
            ->map(fn (string $name) => hex2binary(substr($name, 2, hexdec(substr($name, 0, 2)) * 2)));

        cache()->put('screen_name', $roomList->pop());
        $this->console->setPrompt(cache('screen_name').': ');

        cache()->put('room_list', $roomList);
        $this->console->setAutocomplete(fn () => $roomList->toArray());

        $this->console->write($roomList->implode(', ').' are currently in this room.'.PHP_EOL);
    }

    private function parseRoomMessage(Packet $packet): void
    {
        [$screenName, $message] = str($packet->hex())
            ->substr(20)
            ->whenStartsWith('4f6e6c696e65486f7374', function (Stringable $data) {
                return $data->replace('4f6e6c696e65486f737420', '4f6e6c696e65486f73740000');
            })
            ->whenContains('7f4f6e6c696e65486f73743a09', function (Stringable $data) {
                return $data->replace('7f4f6e6c696e65486f73743a09', '0a4f6e6c696e65486f73743a20');
            })
            ->replaceLast('0000', '|')
            ->explode('|')
            ->map(fn (string $data) => trim(utf8_encode(hex2binary($data))));

        if ($screenName === cache('screen_name')) {
            return;
        }

        if ($this->hasMention($message)) {
            SendDesktopNotification::run($screenName, $message);
        }

        $this->playSoundFromText($message);

        $this->console->write($screenName.': '.$message.PHP_EOL);
    }

    private function parseEntrance(Packet $packet): void
    {
        with(hex2binary(substr($packet->hex(), 22, strlen($packet->hex()) - 24)), function ($screenName) {
            cache(['room_list' => cache('room_list')->push($screenName)->unique()]);
            $this->console->setAutocomplete(fn () => cache('room_list')->toArray());

            $this->console->write($screenName.' has entered the room.'.PHP_EOL);
        });
    }

    private function parseGoodbye(Packet $packet): void
    {
        with(hex2binary(substr($packet->hex(), 22, strlen($packet->hex()) - 24)), function ($screenName) {
            cache(['room_list' => cache('room_list')->reject(fn ($name) => $name === $screenName)]);
            $this->console->setAutocomplete(fn () => cache('room_list')->toArray());

            $this->console->write($screenName.' has left the room.'.PHP_EOL);
        });
    }

    private function hasMention(string $message): bool
    {
        return with(cache('screen_name'), fn ($screenName) => preg_match("/\b{$screenName}\b/i", $message));
    }
}
