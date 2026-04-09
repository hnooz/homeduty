<?php

namespace App\Http\Controllers;

use App\Enums\GroupMemberRole;
use App\Http\Requests\GroupMemberUpdateRequest;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\GroupMember;
use App\Models\User;
use App\Services\Groups\RemoveGroupMember;
use App\Services\Groups\UpdateGroupMemberRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GroupMemberController extends Controller
{
    public function index(Request $request, Group $group): Response
    {
        Gate::authorize('viewMembers', $group);

        $group->load([
            'memberships.user',
            'invitations' => fn ($query) => $query->pending()->latest(),
        ]);

        $registeredUsersByEmail = User::query()
            ->whereIn('email', $group->invitations->pluck('email')->all())
            ->get()
            ->keyBy(fn (User $user): string => strtolower($user->email));

        return Inertia::render('groups/Members', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'ownerId' => $group->owner_id,
                'inviteLink' => $request->user()->can('manageMembers', $group) ? $group->inviteUrl() : null,
            ],
            'members' => $group->memberships
                ->sortByDesc(fn (GroupMember $membership): bool => $membership->role === GroupMemberRole::Admin)
                ->values()
                ->map(fn (GroupMember $membership): array => [
                    'id' => $membership->id,
                    'userId' => $membership->user_id,
                    'name' => $membership->user->name,
                    'email' => $membership->user->email,
                    'role' => $membership->role->value,
                    'roleLabel' => $membership->role->label(),
                    'isOwner' => $membership->group->owner_id === $membership->user_id,
                ]),
            'pendingInvitations' => $group->invitations->map(function (GroupInvitation $invitation) use ($registeredUsersByEmail): array {
                $matchedUser = $registeredUsersByEmail->get(strtolower($invitation->email));

                return [
                    'token' => $invitation->token,
                    'name' => $invitation->name,
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                    'roleLabel' => $invitation->role->label(),
                    'expiresAt' => $invitation->expires_at?->toIso8601String(),
                    'hasRegisteredUser' => (bool) $matchedUser,
                    'registeredUserName' => $matchedUser?->name,
                ];
            }),
            'roleOptions' => collect(GroupMemberRole::cases())->map(fn (GroupMemberRole $role): array => [
                'value' => $role->value,
                'label' => $role->label(),
            ]),
            'canManageMembers' => $request->user()->can('manageMembers', $group),
            'status' => session('status'),
        ]);
    }

    public function update(GroupMemberUpdateRequest $request, Group $group, GroupMember $groupMember, UpdateGroupMemberRole $updateGroupMemberRole): RedirectResponse
    {
        Gate::authorize('manageMembers', $group);

        abort_unless($groupMember->group_id === $group->id, 404);

        $updateGroupMemberRole->handle($groupMember, GroupMemberRole::from($request->validated('role')));

        return to_route('groups.members.index', $group)
            ->with('status', 'member-role-updated');
    }

    public function destroy(Group $group, GroupMember $groupMember, RemoveGroupMember $removeGroupMember): RedirectResponse
    {
        Gate::authorize('manageMembers', $group);

        abort_unless($groupMember->group_id === $group->id, 404);

        $removeGroupMember->handle($groupMember);

        return to_route('groups.members.index', $group)
            ->with('status', 'member-removed');
    }
}
