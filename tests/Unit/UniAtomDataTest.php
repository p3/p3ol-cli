<?php

use App\Parsers\AtomData\Uni;
use App\ValueObjects\Packet;
use Tests\TestPacket;

it('can parse uni_use_last_atom_value', function () {
    $packet = Packet::make(TestPacket::CHAT_ROOM_LEAVE_AT->value);
    $atoms = $packet->atoms()->where('name', 'uni_use_last_atom_value');

    expect(Uni::parse($atoms->first()->name, $atoms->first()->hex))->toBe('man_set_context_index');
});
