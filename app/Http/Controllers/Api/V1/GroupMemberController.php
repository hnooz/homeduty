<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\GroupMemberRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupMemberUpdateRequest;
use App\Http\Resources\GroupMemberResource;
use App\Models\Group;
use App\Models\GroupMember;
use App\Services\Groups\RemoveGroupMember;
use App\Services\Groups\UpdateGroupMemberRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class GroupMemberController extends Controller
{
    public function index(Group $group): AnonymousResourceCollection
    {
        Gate::authorize('viewMembers', $group);

        $members = $group->memberships()
            ->with(['user', 'group'])
            ->get();

        return GroupMemberResource::collection($members);
    }

    public function update(GroupMemberUpdateRequest $request, Group $group, GroupMember $member, UpdateGroupMemberRole $updateGroupMemberRole): GroupMemberResource
    {
        abort_unless($member->group_id === $group->id, 404);

        $member = $updateGroupMemberRole->handle(
            $member,
            GroupMemberRole::from($request->validated('role')),
        );

        return new GroupMemberResource($member->load(['user', 'group']));
    }

    public function destroy(Group $group, GroupMember $member, RemoveGroupMember $removeGroupMember): JsonResponse
    {
        Gate::authorize('manageMembers', $group);

        abort_unless($member->group_id === $group->id, 404);

        $removeGroupMember->handle($member);

        return response()->json([
            'message' => 'Member removed successfully.',
        ]);
    }
}
