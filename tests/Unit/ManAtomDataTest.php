<?php

//@codingStandardsIgnoreStart
use App\Parsers\AtomData\Man;
use App\ValueObjects\Packet;
use Tests\TestPacket;

it('can parse man_set_context_globalid', function () {
    $packet = Packet::make(TestPacket::LB_AT_PACKET->value);
    $atom = $packet->atoms()->firstWhere('name', 'man_set_context_globalid');

    expect(Man::parse($atom->name, $atom->hex))->toBe('32-98');

    $packet = Packet::make(TestPacket::ji_AT_PROFILE_PACKET->value);
    $atom = $packet->atoms()->firstWhere('name', 'man_set_context_globalid');

    expect(Man::parse($atom->name, $atom->hex))->toBe('32-7686');
});

it('can parse man_start_object', function () {
    $packet = Packet::make(TestPacket::Dd_AT_PACKET->value);
    $atoms = $packet->atoms()->where('name', 'man_start_object');

    expect(Man::parse($atoms->first()->name, $atoms->first()->hex))->toBe('ind_group, null');
    expect(Man::parse($atoms->last()->name, $atoms->last()->hex))->toBe('view, null');

    $packet = Packet::make(TestPacket::LB_AT_PACKET->value);
    $atoms = $packet->atoms()->where('name', 'man_start_object');

    expect(Man::parse($atoms->first()->name, $atoms->first()->hex))->toBe('trigger, "0\tdeadend"');
});

it('can parse man_set_context_relative', function () {
    $packet = Packet::make(TestPacket::CHAT_ROOM_ENTER_AT->value);
    $atoms = $packet->atoms()->where('name', 'man_set_context_relative');

    expect(Man::parse($atoms->first()->name, $atoms->first()->hex))->toBe(257);
    expect(Man::parse($atoms->last()->name, $atoms->last()->hex))->toBe(256);
});

it('can parse man_append_data', function () {
    $packet = Packet::make(TestPacket::CHAT_ROOM_ENTER_AT->value);
    $atoms = $packet->atoms()->where('name', 'man_append_data');

    expect(Man::parse($atoms->first()->name, $atoms->first()->hex))->toBe('"OnlineHost:\tGuest5 has entered the room."');
});

it('can parse man_replace_data', function () {
    $packet = Packet::make(TestPacket::ji_AT_PROFILE_PACKET->value);
    $atoms = $packet->atoms()->where('name', 'man_replace_data');

    expect(Man::parse($atoms->first()->name, $atoms->first()->hex))->toBe('"Pyro"');
});
