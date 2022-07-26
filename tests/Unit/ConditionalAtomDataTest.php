<?php

use App\Parsers\AtomData;
use App\ValueObjects\Packet;
use Tests\TestPacket;

it('can parse if_last_return_true_then', function () {
    $packet = Packet::make(TestPacket::CJ_AT_PACKET->value);
    $atom = $packet->atoms()->firstWhere('name', 'if_last_return_true_then');

    expect(AtomData::parse($atom->name, $atom->hex))->toBe('1, 2');

    $packet = Packet::make(TestPacket::CHAT_ROOM_ENTER_AT->value);
    $atom = $packet->atoms()->firstWhere('name', 'if_last_return_true_then');

    expect(AtomData::parse($atom->name, $atom->hex))->toBe('1');
});
