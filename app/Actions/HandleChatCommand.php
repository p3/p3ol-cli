<?php

namespace App\Actions;

use App\Actions\DisplayPeopleInChat;
use App\Actions\SendInstantMessage;
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
            '/packet' => $this->handlePacket($input),
            '/im' => SendInstantMessage::run($console, $connection, $input),
            default =>  $console->write('We could not find a command for that.'.PHP_EOL)
        };
    }

    private function handlePacket(string $input): void
    {
        if (strlen($input) % 2 !== 0 || ! ctype_xdigit($input)) {
            $this->console->write('Invalid packet.'.PHP_EOL);
        }

        $this->connection->write(hex2binary($input));
    }
}
