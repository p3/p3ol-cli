<?php

//@codingStandardsIgnoreStart
use App\Actions\FetchProfile;
use function Clue\React\Block\sleep;
use React\Socket\ConnectionInterface;

it('can fetch a user profile', function () {
    test()->startConsole();

    $fetchProfile = FetchProfile::make();

    $this->client->connect(function (ConnectionInterface $connection) use ($fetchProfile) {
        $fetchProfile->run($this->console, $connection, 'Abc');
    });

    sleep(.1);

    expect($this->server->packet->toHex())->toBe('5a2a2a00247f7fa06a69012800010001070400000020010a0400000001030103416263011d000002000d');

    expect($this->output)->toContain('Member Name');
    expect($this->output)->toContain('Bio');
    expect($this->output)->toContain('Male');
    expect($this->output)->toContain('pyro was here 2022');
    expect($this->output)->toContain('[None]');
});

it('can return that there is no user profile available', function () {
    test()->startConsole();

    $this->server->returnNoProfile = true;

    $this->client->connect(function (ConnectionInterface $connection) {
        FetchProfile::run($this->console, $connection, 'Abc');
    });

    sleep(.1);

    expect($this->server->packet->toHex())->toBe('5a2a2a00247f7fa06a69012800010001070400000020010a0400000001030103416263011d000002000d');

    expect($this->output)->toContain('This user does not have a profile');
});
