<?php

use App\Actions\FetchChatRooms;
use React\Socket\ConnectionInterface;
use function Clue\React\Block\sleep;

it('it can fetch public chatrooms', function () {
    $fetchChatRooms = FetchChatRooms::make();

    $this->client->connect(function (ConnectionInterface $connection) use ($fetchChatRooms) {
        $fetchChatRooms->handle($connection);
    });

    sleep(.1);

    expect(invade($fetchChatRooms)->parseChatrooms()->toArray())->toBe([
        ['people' => 0, 'name' => 'deadend'],
        ['people' => 7, 'name' => 'Welcome'],
        ['people' => 0, 'name' => 'The 8-bit Guy'],
        ['people' => 0, 'name' => 'Tech Linked'],
        ['people' => 0, 'name' => 'Nostalgia Nerd'],
        ['people' => 0, 'name' => 'News'],
    ]);
});
