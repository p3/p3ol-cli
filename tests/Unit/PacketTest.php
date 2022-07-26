<?php

use App\Enums\PacketType;
use App\ValueObjects\Packet;
use Tests\TestPacket;

it('can parse multiple p3 packets from a single packet', function () {
    $packet = Packet::make(TestPacket::Dd_AT_PACKET->value);

    expect($packet->count())->toBe(6);
});

it('can take a p3 packet by position', function () {
    $packet = Packet::make(TestPacket::Dd_AT_PACKET->value);

    expect($packet->takeNumber(1)->count())->toBe(1);
});

it('can parse p3 tokens', function () {
    $packet = Packet::make(TestPacket::Dd_AT_PACKET->value);

    expect($packet->takeNumber(1)->token())->toBe('AT');
    expect($packet->takeNumber(2)->token())->toBe(null);
    expect($packet->takeNumber(3)->token())->toBe('AT');
    expect($packet->takeNumber(4)->token())->toBe(null);
    expect($packet->takeNumber(5)->token())->toBe('AT');
    expect($packet->takeNumber(6)->token())->toBe(null);
});

it('can take p3 packets by token', function () {
    $packet = Packet::make(TestPacket::Dd_AT_PACKET->value);

    $packets = $packet->takeToken('AT');

    expect($packets->count())->toBe(3);
    $packets->each(fn (string $hex) => expect(Packet::make($hex)->token())->toBe('AT'));
});

it('can parse p3 packet type', function () {
    $packet = Packet::make(TestPacket::Dd_AT_PACKET->value);

    expect($packet->takeNumber(1)->type())->toBe(PacketType::DATA);
    expect($packet->takeNumber(2)->type())->toBe(PacketType::ACK);
    expect($packet->takeNumber(3)->type())->toBe(PacketType::DATA);
    expect($packet->takeNumber(4)->type())->toBe(PacketType::ACK);
    expect($packet->takeNumber(5)->type())->toBe(PacketType::DATA);
    expect($packet->takeNumber(6)->type())->toBe(PacketType::ACK);
});

it('can take p3 packets by type', function () {
    $packet = Packet::make(TestPacket::Dd_AT_PACKET->value);

    $packets = $packet->takeType(PacketType::DATA);

    expect($packets->count())->toBe(3);
    $packets->each(fn (string $hex) => expect(Packet::make($hex)->type())->toBe(PacketType::DATA));
});
