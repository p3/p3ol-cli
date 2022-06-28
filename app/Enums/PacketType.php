<?php

namespace App\Enums;

enum PacketType: int
{
    case DATA = 0x20;
    case SS = 0x21;
    case SSR = 0x22;
    case INIT = 0x23;
    case ACK = 0x24;
    case NAK = 0x25;
    case HEARTBEAT = 0x26;
}
