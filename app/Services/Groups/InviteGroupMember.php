<?php

namespace App\Services\Groups;

use App\Enums\GroupMemberRole;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use App\Notifications\HomeGroupInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteGroupMember
{
    /**
     * @param  array{name: string, email: string, phone_number?: string|null, role: GroupMemberRole|string}  $attributes
     */
    public function handle(Group $group, User $invitedBy, array $attributes): GroupInvitation
    {
        $email = Str::lower(trim($attributes['email']));
        $role = $attributes['role'] instanceof GroupMemberRole
            ? $attributes['role']
            : GroupMemberRole::from($attributes['role']);

        if ($group->members()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'That person is already a member of this Home Group.',
            ]);
        }

        if ($group->invitations()->pending()->where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'There is already a pending invitation for that email address.',
            ]);
        }

        /** @var GroupInvitation $invitation */
        $invitation = $group->invitations()->create([
            'invited_by_user_id' => $invitedBy->id,
            'name' => trim($attributes['name']),
            'email' => $email,
            'phone_number' => filled($attributes['phone_number'] ?? null) ? trim((string) $attributes['phone_number']) : null,
            'role' => $role,
            'token' => (string) Str::uuid(),
            'expires_at' => now()->addDays(7),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new HomeGroupInvitationNotification($invitation));

        return $invitation->load(['group', 'invitedBy']);
    }
}
