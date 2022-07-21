<?php

use App\Actions\HandleChatPacket;
use App\ValueObjects\Packet;
use NunoMaduro\LaravelDesktopNotifier\Facades\Notifier;

it('can parse people that are in the chat room', function () {
    $packet = Packet::make(test()->fixture('chat_room.joined'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('reaol, PoSsE4uS, Zip, Guest6ZE, Xak, Guest9 currently in this room.');
});

it('can parse new messages in the chat room', function () {
    cache(['screen_name' => 'abc']);
    $packet = Packet::make(test()->fixture('chat_room.message'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('this is just a test message');
});

it('can highlight messages mentioning screen name in the chat room', function () {
    Notifier::shouldReceive('send');

    cache(['screen_name' => 'test']);
    $packet = Packet::make(test()->fixture('chat_room.message'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain("\e[42mtest\e[0m");
});

it('can highlight messages mentioning handle in the chat room', function () {
    Notifier::shouldReceive('send');

    cache(['chat_handle' => 'just']);
    $packet = Packet::make(test()->fixture('chat_room.message'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain("\e[42mjust\e[0m");
});

it('can parse user leaving chat room', function () {
    cache(['room_list' => collect()]);
    $packet = Packet::make(test()->fixture('chat_room.leave'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('Guest3L4U has left the room.');
});

it('can parse user entering the chat room', function () {
    cache(['room_list' => collect()]);
    $packet = Packet::make(test()->fixture('chat_room.enter'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('Guest5 has entered the room.');
});

it('can receive instant messages', function () {
    $packet = Packet::make(test()->fixture('instant_message.received'));

    HandleChatPacket::run($this->console, $packet);

    expect($this->output)->toContain('Howdy!');
});
