<?php

namespace App\Enums;

enum GroupMemberRole: string
{
    case Admin = 'admin';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Member => 'Member',
        };
    }

    public function toHomeDutyRole(): HomeDutyRole
    {
        return match ($this) {
            self::Admin => HomeDutyRole::GroupAdmin,
            self::Member => HomeDutyRole::GroupMember,
        };
    }
}
