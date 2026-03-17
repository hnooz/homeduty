<?php

namespace App\Services\Groups;

use App\Models\Duty;

class RemoveGroupDuty
{
    public function handle(Duty $duty): void
    {
        $duty->delete();
    }
}
