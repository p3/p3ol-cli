<?php

use App\Actions\LoginAsGuest;
use App\Events\SuccessfulLogin;
use function Clue\React\Block\sleep;
use Illuminate\Support\Facades\Event;

it('it can sign on as a guest', function () {
    Event::fake();

    $this->client->connect(function ($connection) {
        LoginAsGuest::run($connection);
    });

    sleep(.1);

    Event::assertDispatched(SuccessfulLogin::class);
});
