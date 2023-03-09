<?php

namespace App\Actions;

use App\Enums\AtomPacketEvent;
use App\Traits\Sound;
use App\ValueObjects\Atom;
use App\ValueObjects\Packet;
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
            'AT', 'at' => $this->parseAtomStream($packet),
            'AB' => $this->parseRoomMessage($packet),
            default => info($packet->toHex())
        };
    }

    private function parseAtomStream(Packet $packet): void
    {
        match (AtomPacketEvent::event($packet)) {
            AtomPacketEvent::CHAT_ROOM_ENTER => $this->parseEnter($packet),
            AtomPacketEvent::CHAT_ROOM_LEAVE => $this->parseLeave($packet),
            AtomPacketEvent::INSTANT_MESSAGE => HandleInstantMessagePacket::run($this->console, $packet),
            AtomPacketEvent::CHAT_ROOM_PEOPLE => $this->parsePeopleInRoom($packet),
            default => null
        };
    }

    private function parsePeopleInRoom(Packet $packet): void
    {
        $roomList = $packet->atoms()->where('name', 'chat_add_user')->map(fn (Atom $atom) => $atom->toBinary());

        cache()->put('screen_name', $roomList->pop());
        $this->console->setPrompt(cache('screen_name').': ');

        cache()->put('room_list', $roomList);
        $this->console->setAutocomplete(fn () => $roomList->toArray());

        $this->console->write($roomList->implode(', ').' currently in this room.'.PHP_EOL);
    }

    private function parseRoomMessage(Packet $packet): void
    {
        [$screenName, $message] = $packet->takeNumber(1)
            ->toStringableHex()
            ->substr(20)
            ->whenStartsWith('4f6e6c696e65486f7374', function (Stringable $data) {
                return $data->replace('4f6e6c696e65486f737420', '4f6e6c696e65486f73740000');
            })
            ->whenContains('7f4f6e6c696e65486f73743a09', function (Stringable $data) {
                return $data->replace('7f4f6e6c696e65486f73743a09', '0a4f6e6c696e65486f73743a20');
            })
            ->when(true, function (Stringable $string) {
                return str(wordwrap($string, 2, ' ', true));
            })
            ->replaceFirst(' 00 ', '|')
            ->replace(' ', '')
            ->explode('|')
            ->map(fn (string $data) => trim(utf8_encode(hex2binary($data))));

        if ($screenName === cache('screen_name')) {
            return;
        }

        $this->playSoundFromText($message);

        if ($this->hasMention($message)) {
            SendDesktopNotification::run($screenName, $message);

            $message = $this->highlightMention($message);
        }

        $this->console->write($screenName.': '.$message.PHP_EOL);
    }

    private function parseEnter(Packet $packet): void
    {
        $screenName = $packet->atoms()->firstWhere('name', 'chat_add_user')->toBinary();

        cache(['room_list' => cache('room_list')->push($screenName)->unique()]);
        $this->console->setAutocomplete(fn () => cache('room_list')->toArray());

        $this->console->write($screenName.' has entered the room.'.PHP_EOL);
    }

    private function parseLeave(Packet $packet): void
    {
        $screenName = $packet->atoms()->firstWhere('name', 'man_get_index_by_title')->toBinary();

        cache(['room_list' => cache('room_list')->reject(fn ($name) => $name === $screenName)]);
        $this->console->setAutocomplete(fn () => cache('room_list')->toArray());

        $this->console->write($screenName.' has left the room.'.PHP_EOL);
    }

    private function hasMention(string $message): bool
    {
        return count($this->mentions($message)) > 0;
    }

    private function mentions(string $message): array
    {
        $input = implode('|', [cache('screen_name'), cache('chat_handle')]);

        return with(preg_match_all("/\b{$input}\b/i", $message, $matches), function () use ($matches) {
            return collect($matches[0])->filter()->unique()->toArray();
        });
    }

    private function highlightMention(string $message): string
    {
        return with(implode('|', $this->mentions($message)), function ($input) use ($message) {
            return preg_replace("/\b{$input}\b/i", Color::BG_GREEN.'$0'.Color::RESET, $message);
        });
    }
}
