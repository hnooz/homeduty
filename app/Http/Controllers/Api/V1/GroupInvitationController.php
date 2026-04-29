<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupInvitationStoreRequest;
use App\Http\Resources\GroupInvitationResource;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use App\Services\Groups\AcceptGroupInvitation;
use App\Services\Groups\InviteGroupMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class GroupInvitationController extends Controller
{
    public function store(GroupInvitationStoreRequest $request, Group $group, InviteGroupMember $inviteGroupMember): JsonResponse
    {
        $invitation = $inviteGroupMember->handle($group, $request->user(), $request->validated());

        return (new GroupInvitationResource($invitation))
            ->response()
            ->setStatusCode(201);
    }

    public function acceptDirect(Group $group, GroupInvitation $invitation, AcceptGroupInvitation $acceptGroupInvitation): JsonResponse
    {
        Gate::authorize('manageMembers', $group);

        abort_unless($invitation->group_id === $group->id, 404);
        abort_unless($invitation->isPending(), 404);

        $matchedUser = User::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($invitation->email)])
            ->first();

        if (! $matchedUser) {
            $matchedUser = User::query()->create([
                'name' => $invitation->name,
                'email' => $invitation->email,
                'password' => Str::random(32),
            ]);

            Password::sendResetLink(['email' => $matchedUser->email]);
        }

        $acceptGroupInvitation->handle($invitation, $matchedUser);

        return response()->json([
            'message' => 'Invitation accepted directly.',
            'data' => new GroupInvitationResource($invitation->fresh()->load('group')),
        ]);
    }

    public function destroy(Group $group, GroupInvitation $invitation): JsonResponse
    {
        Gate::authorize('manageMembers', $group);

        abort_unless($invitation->group_id === $group->id, 404);

        $invitation->delete();

        return response()->json([
            'message' => 'Invitation cancelled.',
        ]);
    }
}
