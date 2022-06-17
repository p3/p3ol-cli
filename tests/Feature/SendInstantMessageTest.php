<?php

//@codingStandardsIgnoreStart
use App\Actions\SendInstantMessage;
use function Clue\React\Block\sleep;
use Tests\FakeServer;

it('it can join a chatroom', function () {
    $server = new FakeServer();
    test()->startConsole();

    $server->connect(function ($connection) {
        SendInstantMessage::run($this->console, $connection, 'Guest356 how are you?');
    });

    sleep(.1);
    expect($server->lastPacket)->toBe('5a2a2a00421c33a06953005600010001070400000011010a04000000010301084775657374333536011d00010a040000000203010c686f772061726520796f753f011d000002000d');

    $server->close();
});
