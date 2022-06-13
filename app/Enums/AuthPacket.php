<?php
//@codingStandardsIgnoreStart
namespace App\Enums;

enum AuthPacket: string
{
    case VERSION = '5acc3d00347f7fa3036e5f0010000000050f00001c980b3ac3b610c003200000000004000000014006b004ffff0000000000000000000000020d';
    case Dd_PACKET = '5a2a2a003c107fa0446400e8000100010a0400000001010b040000000203010a47756573742020202020011d00011d00010a040000000203010120011d000002000d';
    case SC_PACKET = '5a359e00141112a053430014000100030104000000000002000d';
    case pE_PACKET = '5a2a2a00141319a07045001d000100000e04000000150002000d';
}
