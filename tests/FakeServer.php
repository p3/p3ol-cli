<?php

//@codingStandardsIgnoreStart

namespace Tests;

use App\Enums\AuthPacket;
use App\Enums\ChatroomPacket;
use React\Socket\Connector;
use React\Socket\SocketServer;

class FakeServer
{
    public $server;

    public $lastPacket;

    public function __construct()
    {
        $this->server = new SocketServer('127.0.0.1:5190');

        $this->server->on('connection', function ($connection) {
            $connection->on('data', function ($data) use ($connection) {
                $this->handleData($connection, $data);
            });
        });
    }

    public function close()
    {
        $this->server->close();
    }

    public function connect($callback)
    {
        with(new Connector(), function ($connector) use ($callback) {
            $connector->connect('127.0.0.1:5190')->then(function ($connection) use ($callback) {
                $callback($connection);
            });
        });
    }

    public function handleData($connection, $data): void
    {
        match ($this->lastPacket = bin2hex($data)) {
            AuthPacket::VERSION->value => $connection->write(hex2binary('5ab71100037f7f240d')),
            AuthPacket::Dd_PACKET->value => $connection->write(hex2binary('5a5343')),
            AuthPacket::SC_PACKET->value => $connection->write('Welcome'),
            ChatroomPacket::CJ_PACKET->value => $connection->write(hex2binary('5a215a00b1161220415400180001000109032000620f13020102010a010101000a06300964656164656e64100b010101020001000b0631340957656c636f6d65100b010301020001001006300954686520382d62697420477579100b010401020001000e06300954656368204c696e6b6564100b01050102000100110630094e6f7374616c676961204e657264100b01060102000100070630094e657773100b0107010200011100011d0000070101000701020012000d')),
            default => ''
        };
    }
}
