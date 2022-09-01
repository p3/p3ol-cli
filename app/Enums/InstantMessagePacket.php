<?php

//@codingStandardsIgnoreStart

namespace App\Enums;

enum InstantMessagePacket: string
{
    case iS_PACKET = '5a2a2a003b1317a06953{streamId}000100010704000000{responseId}010a04000000010301{screenName}011d00010a04000000020301{message}011d000002000d';
}
