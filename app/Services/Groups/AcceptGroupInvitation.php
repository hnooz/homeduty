<?php

namespace App\Services\Groups;

use App\Enums\HomeDutyRole;
use App\Models\GroupInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcceptGroupInvitation
{
    public function handle(GroupInvitation $invitation, User $user): void
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation is no longer active.',
            ]);
        }

        if (strtolower($invitation->email) !== strtolower($user->email)) {
            throw ValidationException::withMessages([
                'email' => 'This invitation belongs to another email address.',
            ]);
        }

        if ($user->ownedGroup()->whereKeyNot($invitation->group_id)->exists()) {
            throw ValidationException::withMessages([
                'invitation' => 'This account already owns another Home Group and cannot join a second one.',
            ]);
        }

        if ($user->groupMemberships()->where('group_id', '!=', $invitation->group_id)->exists()) {
            throw ValidationException::withMessages([
                'invitation' => 'This account already belongs to another Home Group and cannot join a second one.',
            ]);
        }

        DB::transaction(function () use ($invitation, $user): void {
            $invitation->group->memberships()->updateOrCreate(
                [
                    'group_id' => $invitation->group_id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $invitation->role,
                ],
            );

            $user->syncRoles($invitation->role->toHomeDutyRole());
            $user->forceFill([
                'is_group_admin' => $invitation->role->toHomeDutyRole() === HomeDutyRole::GroupAdmin,
            ])->saveQuietly();

            $invitation->forceFill([
                'accepted_at' => now(),
                'accepted_by_user_id' => $user->id,
            ])->save();
        });
    }
}
