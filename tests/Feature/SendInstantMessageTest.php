<?php

//@codingStandardsIgnoreStart
use App\Actions\SendInstantMessage;
use function Clue\React\Block\sleep;
use React\Socket\ConnectionInterface;

it('can send an instant message', function () {
    $this->client->connect(function (ConnectionInterface $connection) {
        SendInstantMessage::run($this->console, $connection, 'Guest356 how are you?');
    });

    sleep(.1);

    expect($this->server->packet->toHex())->toBe('5a2a2a00427f7fa06953005600010001070400000000010a04000000010301084775657374333536011d00010a040000000203010c686f772061726520796f753f011d000002000d');
});
