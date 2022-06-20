<?php

namespace App\Actions;

use App\Actions\SendChatMessage;
use Carbon\CarbonInterface;
use Clue\React\Stdio\Stdio;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;

class StartChatIdler
{
    use AsAction;
    use WithAttributes;

    protected float $startTime;

    protected string $ascii = '`v-[...]- cli idle - ';

    public function handle(Stdio $console, ConnectionInterface $connection, string $reason): void
    {
        $this->set('console', $console);
        $this->set('connection', $connection);
        $this->set('reason', $reason);

        if (cache('idle_timer')) {
            return;
        }

        $this->startTime = microtime(true);

        with($this->ascii.'started - reason: '.$reason, function ($message) {
            $this->console->write(cache('screen_name').': '.$message.PHP_EOL);
            SendChatMessage::run($this->connection, $message);
        });

        $timer = Loop::addPeriodicTimer(300, function () {
            with($this->ascii.'reason: '.$this->reason.' - '.$this->time(), function ($message) {
                $this->console->write(cache('screen_name').': '.$message.PHP_EOL);
                SendChatMessage::run($this->connection, $message);
            });
        });

        cache(['idle_timer' => $timer]);
    }

    private function time(): string
    {
        return now()->subSeconds(microtime(true) - $this->startTime)->diffForHumans([
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            'short' => true,
            'parts' => 3,
        ]);
    }
}
