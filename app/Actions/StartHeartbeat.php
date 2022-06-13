<?php

namespace App\Actions;

use App\Events\StopHeartbeat;
use Illuminate\Support\Facades\Event;
use Lorisleiva\Actions\Concerns\AsAction;
use React\EventLoop\Loop;
use React\Socket\ConnectionInterface;

class StartHeartbeat
{
    use AsAction;

    public function handle(ConnectionInterface $connection): void
    {
        with(Loop::get(), function (Loop $loop) use ($connection) {
            $loop->addPeriodicTimer(300, function () use ($connection) {
                // 5a ## ## 00 03 ## ## ## 0d
                $connection->write(hex2bin('5ac93300031847a60d'));
            });

            Event::listen(StopHeartbeat::class, function () use ($loop) {
                $loop->stop();
            });
        });
    }
}
