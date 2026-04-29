<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\GroupUpdateRequest;
use App\Http\Requests\GroupStoreRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class GroupController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $groups = Group::query()
            ->where(function ($query) use ($user): void {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('memberships', fn ($q) => $q->where('user_id', $user->id));
            })
            ->withCount([
                'memberships as members_count',
                'invitations as pending_invitations_count' => fn ($query) => $query->pending(),
                'duties as duties_count',
            ])
            ->orderBy('name')
            ->get();

        return GroupResource::collection($groups);
    }

    public function store(GroupStoreRequest $request, CreateHomeGroup $createHomeGroup): JsonResponse
    {
        $group = $createHomeGroup->handle($request->user(), $request->validated());

        $group->loadCount([
            'memberships as members_count',
            'invitations as pending_invitations_count' => fn ($query) => $query->pending(),
            'duties as duties_count',
        ]);

        return (new GroupResource($group))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Group $group): GroupResource
    {
        Gate::authorize('view', $group);

        $group->load(['owner', 'memberships.user'])
            ->loadCount([
                'memberships as members_count',
                'invitations as pending_invitations_count' => fn ($query) => $query->pending(),
                'duties as duties_count',
            ]);

        return new GroupResource($group);
    }

    public function update(GroupUpdateRequest $request, Group $group): GroupResource
    {
        $group->forceFill([
            'name' => trim($request->validated('name')),
        ])->save();

        return new GroupResource($group->fresh());
    }

    public function destroy(Group $group): JsonResponse
    {
        Gate::authorize('delete', $group);

        $group->delete();

        return response()->json([
            'message' => 'Group deleted successfully.',
        ]);
    }
}
