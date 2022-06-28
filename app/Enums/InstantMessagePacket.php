<?php

//@codingStandardsIgnoreStart

namespace App\Enums;

enum InstantMessagePacket: string
{
    case iS_PACKET = '5a2a2a003d1c33a06953005600010001070400000011010a04000000010301{screenName}011d00010a04000000020301{message}011d000002000d';
}
