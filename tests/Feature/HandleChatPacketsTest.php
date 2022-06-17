<?php

use App\Actions\HandleChatPacket;
use App\DTO\Packet;
use Symfony\Component\Console\Output\ConsoleOutput;
use Termwind\Termwind;

it('can parse people that are in the chatroom', function () {
    test()->startConsole();
    $packet = Packet::make(test()->fixture('chatroom_join_AT_packet'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('GuestUUN, x0r, phantasm, Jason, GuestNQG, GuestNAF, ill, GuestPAO, GuestVR4, Tox, Wubbking, GuestMAS are currently in this room.');
});

it('can parse new messages in the chatroom', function () {
    test()->startConsole();
    $packet = Packet::make(test()->fixture('chatroom_message_AB_packet'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('this is just a test message');
});
