<?php

namespace App\Enums;

enum HomeDutyRole: string
{
    case SuperAdmin = 'super-admin';
    case GroupOwner = 'group-owner';
    case GroupAdmin = 'group-admin';
    case GroupMember = 'group-member';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::GroupOwner => 'Home Group owner',
            self::GroupAdmin => 'Home Group admin',
            self::GroupMember => 'Home Group member',
        };
    }
}
