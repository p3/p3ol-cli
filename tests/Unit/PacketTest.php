<?php

use App\Enums\PacketToken;
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

    expect($packet->takeNumber(1)->token()?->name)->toBe(PacketToken::AT->name);
    expect($packet->takeNumber(2)->token()?->name)->toBe(null);
    expect($packet->takeNumber(3)->token()?->name)->toBe(PacketToken::AT->name);
    expect($packet->takeNumber(4)->token()?->name)->toBe(null);
    expect($packet->takeNumber(5)->token()?->name)->toBe(PacketToken::AT->name);
    expect($packet->takeNumber(6)->token()?->name)->toBe(null);
});

it('can take p3 packets by token', function () {
    $packet = Packet::make(TestPacket::Dd_AT_PACKET->value);

    $packets = $packet->takeToken(PacketToken::AT);

    expect($packets->count())->toBe(3);
    $packets->each(fn (Packet $packet) => expect($packet->token())->toBe(PacketToken::AT));
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
    $packets->each(fn (Packet $packet) => expect($packet->type())->toBe(PacketType::DATA));
});

it('can parse gid from AT packets', function () {
    $packet = Packet::make(TestPacket::CJ_AT_PACKET->value);

    expect($packet->gid())->toBe('32-98');

    $packet = Packet::make(TestPacket::ji_AT_PROFILE_PACKET->value);

    expect($packet->gid())->toBe('32-7686');

    $packet = Packet::make(TestPacket::MAX_GLOBAL_ID_AT_PACKET->value);

    expect($packet->gid())->toBe('255-255-65535');
});

it('returns null when parsing an AT packet that has no gid', function () {
    $packet = Packet::make(TestPacket::ji_AT_NO_PROFILE_PACKET->value);

    expect($packet->gid())->toBe(null);
});
