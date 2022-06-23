<?php

//@codingStandardsIgnoreStart

namespace App\Enums;

enum SignOnState: string
{
    case OFFLINE = 'offline';
    case ONLINE = 'online';
    case AWAITING_WELCOME = 'awaitingWelcome';
    case NEEDS_Dd_PACKET = 'needsDdPacket';
    case NEEDS_SC_PACKET = 'needsScPacket';
}
