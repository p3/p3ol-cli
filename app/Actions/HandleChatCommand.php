<?php

namespace App\Actions;

use App\Events\QuitChat;
use Clue\React\Stdio\Stdio;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\Socket\ConnectionInterface;

class HandleChatCommand
{
    use AsAction;
    use WithAttributes;

    public function handle(Stdio $console, ConnectionInterface $connection, string $input): void
    {
        $this->set('console', $console);
        $this->set('connection', $connection);

        [$command, $input] = parseArguments($input);

        match ($command) {
            '/quit' => QuitChat::dispatch(),
            '/here' => DisplayPeopleInChat::run($console),
            '/packet' => SendPacket::run($console, $connection, $input),
            '/im' => SendInstantMessage::run($console, $connection, $input),
            '/idle' => StartChatIdler::run($console, $connection, $input),
            '/idleoff' => StopChatIdler::run($console, $connection),
            '/handle' => SetChatHandle::run($console, $input),
            '/uptime' => DisplayUptime::run($console, $connection),
            '/profile' => FetchProfile::run($console, $connection, $input),
            '/dump' => DisplayLastAtomPacket::run($console),
            default =>  $console->write('We could not find a command for that.'.PHP_EOL)
        };
    }
}
