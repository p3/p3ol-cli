<?php

//@codingStandardsIgnoreStart
use App\Actions\FetchProfile;
use App\ValueObjects\Packet;
use function Clue\React\Block\sleep;
use React\Socket\ConnectionInterface;
use Tests\TestPacket;

it('can fetch a user profile', function () {
    $this->client->connect(function (ConnectionInterface $connection) {
        FetchProfile::run($this->console, $connection, 'Abc');
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
    $this->server->respondWith(TestPacket::ji_AT_NO_PROFILE_PACKET->value);

    $this->client->connect(function (ConnectionInterface $connection) {
        FetchProfile::run($this->console, $connection, 'Abc');
    });

    sleep(.1);

    expect($this->server->packet->toHex())->toBe('5a2a2a00247f7fa06a69012800010001070400000020010a0400000001030103416263011d000002000d');

    expect($this->output)->toContain('This user does not have a profile');
});

it('can fetch a profile that has no bio', function () {
    $fetchProfile = FetchProfile::make();

    $packet = Packet::make(TestPacket::ji_AT_PROFILE_WITH_NO_BIO->value);

    $this->server->respondWith($packet->toHex());

    $this->client->connect(function (ConnectionInterface $connection) use ($fetchProfile) {
        $fetchProfile->handle($this->console, $connection, 'Abc');
    });

    sleep(.1);

    expect(invade($fetchProfile)->parseBio($packet))->toBe(['Bio' => '[None]']);
});
