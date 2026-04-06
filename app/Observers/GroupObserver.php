<?php

namespace App\Observers;

use App\Enums\HomeDutyRole;
use App\Models\Group;
use App\Models\User;
use Filament\Notifications\Notification;

class GroupObserver
{
    public function created(Group $group): void
    {
        $this->notifySuperAdmins(
            "New group created: {$group->name}",
            "Created by {$group->owner?->name}.",
        );
    }

    public function deleted(Group $group): void
    {
        $this->notifySuperAdmins(
            "Group deleted: {$group->name}",
            'The group has been soft-deleted.',
        );
    }

    private function notifySuperAdmins(string $title, string $body): void
    {
        $superAdmins = User::role(HomeDutyRole::SuperAdmin->value)->get();

        Notification::make()
            ->title($title)
            ->body($body)
            ->info()
            ->sendToDatabase($superAdmins);
    }
}
