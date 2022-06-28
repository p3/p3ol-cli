<?php

namespace Tests;

use App\Actions\IncreasePacketSequence;
use App\ValueObjects\Packet;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class FakeClient
{
    public function connect(callable $callback): void
    {
        with(new Connector(), function (Connector $connector) use ($callback) {
            $connector->connect('127.0.0.1:5191')->then(function (ConnectionInterface $connection) use ($callback) {
                $connection->on('data', function (string $data) {
                    IncreasePacketSequence::run(Packet::make($data));
                });

                $callback($connection);
            });
        });
    }
}
