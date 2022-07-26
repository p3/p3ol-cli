<?php

use App\ValueObjects\Atom;
use App\ValueObjects\Packet;
use Tests\TestPacket;

it('can parse an atom packet that only uses full-style (0) atoms', function () {
    $packet = Packet::make(TestPacket::CHAT_ROOM_ENTER_AT->value);

    expect($packet->atoms()->toArray())->toMatchArray([
        new Atom('uni_start_stream', null, null),
        new Atom('man_set_context_globalid', '13000002', '19-0-2'),
        new Atom('if_last_return_true_then', '01', '1'),
        new Atom('man_set_context_relative', '00000101', '257'),
        new Atom('chat_add_user', '477565737435', '477565737435'),
        new Atom('mat_relative_tag', '00000fa0', '00000fa0'),
        new Atom('act_set_inheritance', '02', '02'),
        new Atom('chat_end_object', null, null),
        new Atom('man_end_context', null, null),
        new Atom('man_set_context_relative', '00', '0'),
        new Atom('act_do_action', '0082', '0082'),
        new Atom('man_end_context', null, null),
        new Atom('act_get_db_value', '14000025', '14000025'),
        new Atom('if_last_return_true_then', '02', '2'),
        new Atom('man_set_context_relative', '00000100', '256'),
        new Atom('man_append_data', '7f4f6e6c696e65486f73743a094775657374352068617320656e74657265642074686520726f6f6d2e', '"OnlineHost:\tGuest5 has entered the room."'),
        new Atom('man_end_context', null, null),
        new Atom('uni_sync_skip', '02', '02'),
        new Atom('man_update_display', null, null),
        new Atom('uni_sync_skip', '01', '01'),
        new Atom('man_place_cursor', '00000102', '00000102'),
        new Atom('man_end_context', null, null),
        new Atom('uni_wait_off_end_stream', null, null),
    ]);
});

it('can parse an atom packet and format as FDO', function () {
    $packet = Packet::make(TestPacket::CHAT_ROOM_ENTER_AT->value);

    $output = <<<'HTML'
 uni_start_stream <>
   man_set_context_globalid <19-0-2>
   if_last_return_true_then <1>
   man_set_context_relative <257>
   chat_add_user <47x, 75x, 65x, 73x, 74x, 35x>
   mat_relative_tag <00x, 00x, 0fx, a0x>
   act_set_inheritance <02x>
   chat_end_object <>
   man_end_context <>
   man_set_context_relative <0>
   act_do_action <00x, 82x>
   man_end_context <>
   act_get_db_value <14x, 00x, 00x, 25x>
   if_last_return_true_then <2>
   man_set_context_relative <256>
   man_append_data <"OnlineHost:\tGuest5 has entered the room.">
   man_end_context <>
   uni_sync_skip <02x>
   man_update_display <>
   uni_sync_skip <01x>
   man_place_cursor <00x, 00x, 01x, 02x>
   man_end_context <>
 uni_wait_off_end_stream <>

HTML;

    expect($packet->toFDO())->toEqual($output);
});
