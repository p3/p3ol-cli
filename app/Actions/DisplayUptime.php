<?php

namespace App\Actions;

use Carbon\CarbonInterface;
use Clue\React\Stdio\Stdio;
use Lorisleiva\Actions\Concerns\AsAction;
use React\Socket\ConnectionInterface;

class DisplayUptime
{
    use AsAction;

    public function handle(Stdio $console, ConnectionInterface $connection): void
    {
        $console->write(cache('screen_name').': '.$this->message().PHP_EOL);
        SendChatMessage::run($connection, $this->message());
    }

    private function message(): string
    {
        return '`v-[...]- I have been online for '.$this->time();
    }

    private function time(): string
    {
        return now()->subSeconds(microtime(true) - cache('start_time'))->diffForHumans([
            'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            'parts' => 3,
        ]);
    }
}
