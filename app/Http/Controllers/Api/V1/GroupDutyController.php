<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupDutyStoreRequest;
use App\Http\Requests\GroupDutyUpdateRequest;
use App\Http\Resources\DutyResource;
use App\Models\Duty;
use App\Models\Group;
use App\Services\Groups\CreateGroupDuty;
use App\Services\Groups\RemoveGroupDuty;
use App\Services\Groups\UpdateGroupDuty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class GroupDutyController extends Controller
{
    public function index(Group $group): AnonymousResourceCollection
    {
        Gate::authorize('viewDuties', $group);

        $duties = $group->duties()
            ->with(['members', 'slots.user'])
            ->orderBy('starts_on')
            ->get();

        return DutyResource::collection($duties);
    }

    public function store(GroupDutyStoreRequest $request, Group $group, CreateGroupDuty $createGroupDuty): JsonResponse
    {
        $duty = $createGroupDuty->handle($group, $request->validated());

        return (new DutyResource($duty->load(['members', 'slots.user'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Group $group, Duty $duty): DutyResource
    {
        Gate::authorize('viewDuties', $group);

        abort_unless($duty->group_id === $group->id, 404);

        return new DutyResource($duty->load(['members', 'slots.user']));
    }

    public function update(GroupDutyUpdateRequest $request, Group $group, Duty $duty, UpdateGroupDuty $updateGroupDuty): DutyResource
    {
        abort_unless($duty->group_id === $group->id, 404);

        $duty = $updateGroupDuty->handle($duty, $request->validated());

        return new DutyResource($duty->load(['members', 'slots.user']));
    }

    public function destroy(Group $group, Duty $duty, RemoveGroupDuty $removeGroupDuty): JsonResponse
    {
        Gate::authorize('manageDuties', $group);

        abort_unless($duty->group_id === $group->id, 404);

        $removeGroupDuty->handle($duty);

        return response()->json([
            'message' => 'Duty removed successfully.',
        ]);
    }
}
