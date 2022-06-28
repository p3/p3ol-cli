<?php

namespace App\Enums;

enum PacketToken: string
{
    case AT = 'Atom stream';
    case Dd = 'Form based login packet';
    case SC = 'Sign on sequence complete';
    case pE = 'Invokes the exit screen';
    case CJ = 'View peoples connection';
    case cQ = 'Join a public chat room';
    case CA = 'User joined chat room';
    case CB = 'User has left chat room';
    case Aa = 'Send a chat room message';
    case AB = 'Incoming chat message';
    case iS = 'Send an instant message';
    case ji = 'Display member profile';

    public static function fromString(string $value): ?self
    {
        return collect(self::cases())->firstWhere(fn ($case) => $case->name === $value);
    }
}
