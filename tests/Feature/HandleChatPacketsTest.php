<?php

use App\Actions\HandleChatPacket;
use App\Helpers\Packet;

it('can parse people that are in the chat room', function () {
    test()->startConsole();
    $packet = Packet::make(test()->fixture('chat_room.joined'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('GuestUUN, x0r, phantasm, Jason, GuestNQG, GuestNAF, ill, GuestPAO, GuestVR4, Tox, Wubbking, GuestMAS are currently in this room.');
});

it('can parse new messages in the chat room', function () {
    test()->startConsole();
    cache(['screen_name' => 'abc']);
    $packet = Packet::make(test()->fixture('chat_room.message'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('this is just a test message');
});

it('can highlight messages mentioning screen name in the chat room', function () {
    test()->startConsole();
    cache(['screen_name' => 'test']);
    $packet = Packet::make(test()->fixture('chat_room.message'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain("\e[42mtest\e[0m");
});

it('can highlight messages mentioning handle in the chat room', function () {
    test()->startConsole();
    cache(['chat_handle' => 'just']);
    $packet = Packet::make(test()->fixture('chat_room.message'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain("\e[42mjust\e[0m");
});

it('can receive instant messages', function () {
    test()->startConsole();

    $packet = Packet::make(test()->fixture('instant_message.received'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('Howdy!');
});
