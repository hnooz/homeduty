<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum GroupMemberRole: string implements HasLabel
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

    public function getLabel(): string
    {
        return $this->label();
    }

    public function toHomeDutyRole(): HomeDutyRole
    {
        return match ($this) {
            self::Admin => HomeDutyRole::GroupAdmin,
            self::Member => HomeDutyRole::GroupMember,
        };
    }
}
