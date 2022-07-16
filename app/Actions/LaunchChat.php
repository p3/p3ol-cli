<?php

namespace App\Actions;

use App\ValueObjects\Packet;
use Clue\React\Stdio\Stdio;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\Socket\ConnectionInterface;
use function Termwind\{terminal}; //@codingStandardsIgnoreLine

class LaunchChat
{
    use AsAction;
    use WithAttributes;

    protected Stdio $console;

    public function handle(ConnectionInterface $connection, string $roomName): void
    {
        $this->set('connection', $connection);
        $this->set('roomName', $roomName);

        JoinChat::run($connection, $roomName);

        $this->startConsole();

        $connection->on('data', function (string $data) {
            with(Packet::make($data), fn (Packet $packet) => HandleChatPacket::run($this->console, $packet));
        });
    }

    private function startConsole(): void
    {
        $this->console = new Stdio();

        collect()->times(terminal()->height(), fn () =>  $this->console->write(PHP_EOL));

        $this->console->on('data', fn ($line) => $this->parseConsoleInput(rtrim($line, "\r\n")));

        $this->console->on("\033[B", function () {
            if (cache('last_instant_messaged')) {
                $this->console->setInput('/im '.cache('last_instant_messaged').' ');
            }
        });
    }

    private function parseConsoleInput(string $input): void
    {
        match (true) {
            substr($input, 0, 6) === '//roll' => SendChatMessage::run($this->connection, $input),
            substr($input, 0, 7) === '//8ball' => SendChatMessage::run($this->connection, $input),
            substr($input, 0, 1) === '/' => HandleChatCommand::run($this->console, $this->connection, $input),
            default => SendChatMessage::run($this->connection, $input),
        };
    }
}
