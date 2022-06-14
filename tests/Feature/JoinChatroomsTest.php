<?php

use App\Actions\JoinChatroom;
use function Clue\React\Block\sleep;
use Tests\FakeServer;

it('it can join a chatroom', function () {
    $server = new FakeServer();

    $server->connect(function ($connection) {
        JoinChatroom::run($connection, 'vb');
    });

    sleep(.1);
    expect($server->lastPacket)->toBe('5ac2080019131ba0635100200001000107040000000403010276620002000d');

    $server->close();
});
