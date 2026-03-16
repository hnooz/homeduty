<?php

namespace App\Enums;

enum HomeDutyPermission: string
{
    case CreateHomeGroup = 'create-home-group';
    case ManageHomeGroupMembers = 'manage-home-group-members';
}
