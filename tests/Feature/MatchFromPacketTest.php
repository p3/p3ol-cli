<?php

use App\Enums\AtomPacket;
use App\ValueObjects\Packet;

it('can match to an atom stream packet and grab a match position', function () {
    $match = Packet::make(fixture('chat_room.exit'))->takeNumber(1)
        ->toStringableHex()
        ->matchFromPacket(AtomPacket::CHAT_ROOM_LEAVE, 3);

    expect($match->value)->toBe('094775657374334c3455');
});
