<?php

namespace App\Http\Controllers;

use App\Enums\GroupMemberRole;
use App\Http\Requests\GroupStoreRequest;
use App\Models\Group;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function create(): Response
    {
        Gate::authorize('create', Group::class);

        return Inertia::render('groups/Create');
    }

    public function store(GroupStoreRequest $request, CreateHomeGroup $createHomeGroup): RedirectResponse
    {
        $group = $createHomeGroup->handle($request->user(), $request->validated());

        return to_route('dashboard')
            ->with('status', 'home-group-created')
            ->with('home_group_name', $group->name);
    }

    public function regenerateInviteLink(Request $request, Group $group): RedirectResponse
    {
        Gate::authorize('manageMembers', $group);

        $group->regenerateInviteToken();

        return to_route('groups.members.index', $group)
            ->with('status', 'invite-link-regenerated');
    }

    public function joinViaLink(Request $request, string $token): RedirectResponse
    {
        $group = Group::query()->where('invite_token', $token)->firstOrFail();
        $user = $request->user();

        if ($group->owner_id === $user->id) {
            return to_route('groups.members.index', $group);
        }

        if ($user->groupMemberships()->where('group_id', $group->id)->exists()) {
            return to_route('groups.members.index', $group)
                ->with('status', 'already-a-member');
        }

        if ($user->ownedGroup()->exists() || $user->groupMemberships()->exists()) {
            return to_route('dashboard')
                ->with('status', 'already-in-another-group');
        }

        DB::transaction(function () use ($group, $user): void {
            $group->memberships()->create([
                'user_id' => $user->id,
                'role' => GroupMemberRole::Member,
            ]);

            $newRole = GroupMemberRole::Member->toHomeDutyRole();
            $user->syncRoles($newRole->value);
            $user->forceFill(['is_group_admin' => false])->saveQuietly();
        });

        return to_route('groups.members.index', $group)
            ->with('status', 'joined-via-link');
    }
}
