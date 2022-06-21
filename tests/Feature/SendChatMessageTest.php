<?php

//@codingStandardsIgnoreStart
use App\Actions\SendChatMessage;
use function Clue\React\Block\sleep;
use Tests\FakeServer;

it('it can send a message to chat', function () {
    $server = new FakeServer();

    $server->connect(function ($connection) {
        SendChatMessage::run($connection, 'hey abc what is up my friend');
    });

    sleep(.1);
    expect($server->lastPacket)->toBe('5a8136003a161ea04161012a0001000107040000001b010a040000010203011c686579206162632077686174206973207570206d7920667269656e640002000d');

    $server->close();
});
