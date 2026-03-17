<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupInvitationStoreRequest;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use App\Services\Groups\AcceptGroupInvitation;
use App\Services\Groups\InviteGroupMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GroupInvitationController extends Controller
{
    public function store(GroupInvitationStoreRequest $request, Group $group, InviteGroupMember $inviteGroupMember): RedirectResponse
    {
        Gate::authorize('manageMembers', $group);

        $inviteGroupMember->handle($group, $request->user(), $request->validated());

        return to_route('groups.members.index', $group)
            ->with('status', 'member-invited');
    }

    public function destroy(Group $group, GroupInvitation $groupInvitation): RedirectResponse
    {
        Gate::authorize('manageMembers', $group);

        abort_unless($groupInvitation->group_id === $group->id, 404);

        $groupInvitation->delete();

        return to_route('groups.members.index', $group)
            ->with('status', 'invitation-cancelled');
    }

    public function show(Request $request, GroupInvitation $groupInvitation): Response
    {
        abort_unless($groupInvitation->isPending(), 404);
        abort_unless(strtolower($request->user()->email) === strtolower($groupInvitation->email), 403);

        return Inertia::render('groups/AcceptInvitation', [
            'invitation' => [
                'groupName' => $groupInvitation->group->name,
                'name' => $groupInvitation->name,
                'email' => $groupInvitation->email,
                'role' => $groupInvitation->role->value,
                'roleLabel' => $groupInvitation->role->label(),
                'token' => $groupInvitation->token,
                'expiresAt' => $groupInvitation->expires_at?->toIso8601String(),
            ],
        ]);
    }

    public function accept(Request $request, GroupInvitation $groupInvitation, AcceptGroupInvitation $acceptGroupInvitation): RedirectResponse
    {
        abort_unless($groupInvitation->isPending(), 404);
        abort_unless(strtolower($request->user()->email) === strtolower($groupInvitation->email), 403);

        $acceptGroupInvitation->handle($groupInvitation, $request->user());

        return to_route('dashboard')
            ->with('status', 'invitation-accepted');
    }

    public function acceptDirect(Group $group, GroupInvitation $groupInvitation, AcceptGroupInvitation $acceptGroupInvitation): RedirectResponse
    {
        Gate::authorize('manageMembers', $group);

        abort_unless($groupInvitation->group_id === $group->id, 404);
        abort_unless($groupInvitation->isPending(), 404);

        $matchedUser = User::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($groupInvitation->email)])
            ->first();

        if (! $matchedUser) {
            throw ValidationException::withMessages([
                'invitation' => 'This invitation can only be accepted directly after the invited person registers or signs in.',
            ]);
        }

        $acceptGroupInvitation->handle($groupInvitation, $matchedUser);

        return to_route('groups.members.index', $group)
            ->with('status', 'member-accepted-directly');
    }
}
