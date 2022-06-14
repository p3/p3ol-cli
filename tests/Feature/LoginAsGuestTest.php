<?php

use App\Actions\LoginAsGuest;
use App\Events\SuccessfulLogin;
use function Clue\React\Block\sleep;
use Illuminate\Support\Facades\Event;
use Tests\FakeServer;

it('it can sign on as a guest', function () {
    Event::fake();
    $server = new FakeServer();

    $server->connect(function ($connection) {
        LoginAsGuest::run($connection);
    });

    sleep(.1);
    Event::assertDispatched(SuccessfulLogin::class);
    $server->close();
});
