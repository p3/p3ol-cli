<?php

//@codingStandardsIgnoreStart
use App\Actions\SendChatMessage;
use function Clue\React\Block\sleep;
use React\Socket\ConnectionInterface;

it('it can send a message to chat', function () {
    $this->client->connect(function (ConnectionInterface $connection) {
        SendChatMessage::run($connection, 'hey abc what is up my friend');
    });

    sleep(.1);

    expect($this->server->packet->toHex())->toBe('5a2a2a003a7f7fa04161012a0001000107040000001b010a040000010203011c686579206162632077686174206973207570206d7920667269656e640002000d');
});
