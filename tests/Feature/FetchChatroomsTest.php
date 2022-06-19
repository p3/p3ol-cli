<?php

use App\Actions\FetchChatRooms;
use function Clue\React\Block\sleep;
use Tests\FakeServer;

it('it can fetch public chatrooms', function () {
    $server = new FakeServer();
    $fetchChatRooms = FetchChatRooms::make();

    $server->connect(function ($connection) use ($fetchChatRooms) {
        $fetchChatRooms->handle($connection);
    });

    sleep(.1);
    expect(invade($fetchChatRooms)->parseChatrooms()->toArray())->toBe([
        ['people' => 0, 'name' => 'deadend'],
        ['people' => 14, 'name' => 'Welcome'],
        ['people' => 0, 'name' => 'The 8-bit Guy'],
        ['people' => 0, 'name' => 'Tech Linked'],
        ['people' => 0, 'name' => 'Nostalgia Nerd'],
        ['people' => 0, 'name' => 'News'],
    ]);

    $server->close();
});
