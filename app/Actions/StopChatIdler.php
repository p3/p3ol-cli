<?php

namespace App\Actions;

use Clue\React\Stdio\Stdio;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;

class StopChatIdler
{
    use AsAction;
    use WithAttributes;

    protected string $ascii = '`v-[...]- cli idle - ';

    public function handle(Stdio $console, ConnectionInterface $connection): void
    {
        $this->set('console', $console);
        $this->set('connection', $connection);

        if (! cache('idle_timer')) {
            return;
        }

        Loop::cancelTimer(cache('idle_timer'));
        cache()->forget('idle_timer');

        with($this->ascii.'stopped', function ($message) {
            $this->console->write(cache('screen_name').': '.$message.PHP_EOL);
            SendChatMessage::run($this->connection, $message);
        });
    }
}
