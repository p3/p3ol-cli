<?php

namespace App\Enums;

use App\ValueObjects\Packet;

enum AtomPacketEvent
{
    case INSTANT_MESSAGE;
    case CHAT_ROOM_ENTER;
    case CHAT_ROOM_LEAVE;
    case CHAT_ROOM_PEOPLE;
    case NOT_FOUND;

    public static function event(Packet $packet): self
    {
        return match (true) {
            self::isInstantMessage($packet) => self::INSTANT_MESSAGE,
            self::isChatRoomOpen($packet) => self::CHAT_ROOM_PEOPLE,
            self::isChatRoomLeave($packet) => self::CHAT_ROOM_LEAVE,
            self::isChatRoomEnter($packet) => self::CHAT_ROOM_ENTER,
            default => self::NOT_FOUND,
        };
    }

    private static function isInstantMessage(Packet $packet): bool
    {
        return $packet->atoms()->where('name', 'man_do_magic_response_id')->contains(function ($atom): bool {
            return str($atom->data)->is('000020e1');
        });
    }

    private static function isChatRoomOpen(Packet $packet): bool
    {
        if ($packet->atoms()->firstWhere('name', 'man_set_context_globalid')?->data !== '19-0-2') {
            return false;
        }

        return $packet->atoms()->contains('name', 'chat_room_open');
    }

    private static function isChatRoomLeave(Packet $packet): bool
    {
        if ($packet->atoms()->firstWhere('name', 'man_set_context_globalid')?->data !== '19-0-2') {
            return false;
        }

        if ($packet->atoms()->contains('name', 'chat_room_open')) {
            return false;
        }

        return $packet->atoms()->contains('name', 'man_get_index_by_title');
    }

    private static function isChatRoomEnter(Packet $packet): bool
    {
        if ($packet->atoms()->firstWhere('name', 'man_set_context_globalid')?->data !== '19-0-2') {
            return false;
        }

        if ($packet->atoms()->contains('name', 'chat_room_open')) {
            return false;
        }

        return $packet->atoms()->contains('name', 'chat_add_user');
    }
}
