<?php

//@codingStandardsIgnoreStart

namespace App\Enums;

enum SignOnState: string
{
    case OFFLINE = 'offline';
    case ONLINE = 'online';
    case INVALID = 'invalid';
    case AWAITING_WELCOME = 'awaitingWelcome';
    case NEEDS_Dd = 'needsDdPacket';
    case NEEDS_SC = 'needsScPacket';
}
