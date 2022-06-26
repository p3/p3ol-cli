<?php

use App\Actions\Login;
use App\Events\SuccessfulLogin;
use Illuminate\Support\Facades\Event;
use App\Events\InvalidLogin;
use function Clue\React\Block\sleep;

it('can sign on as a guest', function () {
    Event::fake();

    $this->client->connect(function ($connection) {
        Login::run($connection, ['guest', null]);
    });

    sleep(.1);

    Event::assertDispatched(SuccessfulLogin::class);
});

it('can sign on with a username and password', function () {
    Event::fake();

    $this->client->connect(function ($connection) {
        Login::run($connection, ['AzureDiamond', 'hunter2']);
    });

    sleep(.1);

    Event::assertDispatched(SuccessfulLogin::class);
});

it('can receive an invalid username and password', function () {
    Event::fake();
    $this->server->returnInvalidLogin = true;

    $this->client->connect(function ($connection) {
        Login::run($connection, ['AzureDiamond', 'hunter3']);
    });

    sleep(.1);

    Event::assertDispatched(InvalidLogin::class);
});
