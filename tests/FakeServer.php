<?php

//@codingStandardsIgnoreStart

namespace Tests;

use App\Enums\PacketToken;
use App\ValueObjects\Packet;
use React\Socket\ConnectionInterface;
use React\Socket\SocketServer;

class FakeServer
{
    protected SocketServer $server;

    protected ConnectionInterface $connection;

    protected ?string $respondWith = null;

    public bool $returnInvalidLogin = false;

    public Packet $packet;

    public function __construct()
    {
        $this->server = new SocketServer('127.0.0.1:5191');

        $this->server->on('connection', function ($connection) {
            $this->connection = $connection;
            $connection->on('data', function ($data) {
                $this->incrementServerSequence(Packet::make($data));
                $this->handlePacket($data);
            });
        });
    }

    public function close(): void
    {
        $this->server->close();
    }

    public function respondWith(string $packet): void
    {
        $this->respondWith = $packet;
    }

    public function handlePacket(string $data): void
    {
        $this->packet = Packet::make($data);

        if ($this->respondWith) {
            $this->sendPacket($this->respondWith);
            $this->respondWith = null;

            return;
        }

        match ($this->packet->token()?->name) {
            PacketToken::Dd->name => $this->sendDdPacket(),
            PacketToken::ji->name => $this->sendPacket(TestPacket::ji_AT_PROFILE_PACKET->value),
            PacketToken::SC->name => $this->sendPacket(TestPacket::SC_AT_PACKET->value),
            PacketToken::uD->name => $this->sendPacket(TestPacket::uD_AT_PACKET->value),
            PacketToken::Aa->name => $this->sendPacket(TestPacket::AB_PACKET->value),
            PacketToken::CJ->name => $this->sendPacket(TestPacket::CJ_AT_PACKET->value),
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

    private function sendDdPacket(): void
    {
        match ($this->returnInvalidLogin) {
            true => $this->sendPacket(TestPacket::Dd_INVALID_AT_PACKET->value),
            default => $this->sendPacket(TestPacket::Dd_AT_PACKET->value)
        };
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
