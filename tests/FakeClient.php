<?php

namespace Tests;

use React\Socket\ConnectionInterface;
use App\Helpers\Packet;
use React\Socket\Connector;

class FakeClient
{
    public function connect(callable $callback): void
    {
        with(new Connector(), function (Connector $connector) use ($callback) {
            $connector->connect('127.0.0.1:5191')->then(function (ConnectionInterface $connection) use ($callback) {
                $connection->on('data', function (string $data) {
                    Packet::make($data)->incrementSequence();
                });

                $callback($connection);
            });
        });
    }
}
