<?php
//@codingStandardsIgnoreStart
namespace App\Enums;

/**
 * These are the portions of the AT packet we use to idenfity the type of atom stream.
 */
enum AtomPacket: string
{
    case INSTANT_MESSAGE = '11d00010a01010302010003030101030900000c00030700010a0100001500000a020114011d00010a01030114';
    case INSTANT_MESSAGE_END = '0114017f011100011d00010a010002010120011d000012000d';
    case CHATROOM_LIST = '100b01010b0200011d000b01';
}