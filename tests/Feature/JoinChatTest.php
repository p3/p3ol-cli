<?php

use App\Actions\JoinChat;
use function Clue\React\Block\sleep;
use React\Socket\ConnectionInterface;

it('it can join a chat room', function () {
    $this->client->connect(function (ConnectionInterface $connection) {
        JoinChat::run($connection, 'vb');
    });

    sleep(.1);

    expect($this->server->packet->toHex())->toBe('5a2a2a00197f7fa0635100200001000107040000000403010276620002000d');
});
