<?php

use App\Actions\HandleChatPacket;
use App\DTO\Packet;

it('can parse people that are in the chatroom', function () {
    test()->startConsole();
    $packet = Packet::make(test()->fixture('chat_room.joined'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('GuestUUN, x0r, phantasm, Jason, GuestNQG, GuestNAF, ill, GuestPAO, GuestVR4, Tox, Wubbking, GuestMAS are currently in this room.');
});

it('can parse new messages in the chatroom', function () {
    test()->startConsole();
    $packet = Packet::make(test()->fixture('chat_room.message'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('this is just a test message');
});

it('can receive instant messages', function () {
    test()->startConsole();

    $packet = Packet::make(test()->fixture('instant_message.received'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('Howdy!');
});
