<?php

//@codingStandardsIgnoreStart

namespace Tests;

use App\Helpers\Packet;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;

class FakeServer
{
    protected SocketServer $server;

    protected ConnectionInterface $connection;

    public Packet $packet;

    public function __construct()
    {
        $this->server = new SocketServer('127.0.0.1:5191');

        $this->server->on('connection', function ($connection) {
            $this->connection = $connection;
            $connection->on('data', function ($data) use ($connection) {
                $this->incrementServerSequence(Packet::make($data));
                $this->handlePacket($data);
            });
        });
    }

    public function close(): void
    {
        $this->server->close();
    }

    public function handlePacket(string $data): void
    {
        $this->packet = Packet::make($data);

        match ($this->packet->token()) {
            'Dd' => $this->sendPacket(TestPacket::Dd_AT_PACKET->value),
            'SC' => $this->sendPacket(TestPacket::SC_AT_PACKET->value),
            'Aa' => $this->sendPacket(TestPacket::AB_PACKET->value),
            'CJ' => $this->sendPacket(TestPacket::CJ_AT_PACKET->value),
            default => $this->sendInitAckPacket()
        };
    }

    private function incrementServerSequence(Packet $packet): void
    {
        with(hexdec(substr($packet->toHex(), 12, 2)), function (int $rx) {
            $rx === 127 ? cache(['rx_sequence' => 16]) : cache(['rx_sequence' => $rx + 1]);
        });
    }

    private function sendPacket(string $packet): void
    {
        $this->connection->write(hex2bin($this->sequence($packet)));
    }

    private function sendInitAckPacket(): void
    {
        $this->connection->write(hex2bin(TestPacket::INIT_ACK_PACKET->value));
    }

    private function sequence(string $packet): string
    {
        return substr_replace($packet, dechex($this->rx()).dechex($this->tx()), 10, 4);
    }

    private function rx(): int
    {
        return cache('rx_sequence', 127);
    }

    private function tx(): int
    {
        return cache('tx_sequence', 127);
    }
}
