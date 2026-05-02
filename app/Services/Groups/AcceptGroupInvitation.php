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
        $isSuperAdmin = $user->hasRole(HomeDutyRole::SuperAdmin->value);

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

        if (! $isSuperAdmin && $user->ownedGroup()->whereKeyNot($invitation->group_id)->exists()) {
            throw ValidationException::withMessages([
                'invitation' => 'This account already owns another Home Group and cannot join a second one.',
            ]);
        }

        if (! $isSuperAdmin && $user->groupMemberships()->where('group_id', '!=', $invitation->group_id)->exists()) {
            throw ValidationException::withMessages([
                'invitation' => 'This account already belongs to another Home Group and cannot join a second one.',
            ]);
        }

        DB::transaction(function () use ($invitation, $user, $isSuperAdmin): void {
            $invitation->group->memberships()->updateOrCreate(
                [
                    'group_id' => $invitation->group_id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $invitation->role,
                ],
            );

            $newRole = $invitation->role->toHomeDutyRole();

            foreach ([HomeDutyRole::GroupAdmin, HomeDutyRole::GroupMember] as $groupRole) {
                if ($groupRole !== $newRole && $user->hasRole($groupRole->value)) {
                    $user->removeRole($groupRole->value);
                }
            }

            $user->assignRole($newRole->value);

            $user->forceFill([
                'is_group_admin' => $isSuperAdmin || $newRole === HomeDutyRole::GroupAdmin,
            ])->saveQuietly();

            $invitation->forceFill([
                'accepted_at' => now(),
                'accepted_by_user_id' => $user->id,
            ])->save();
        });
    }
}
