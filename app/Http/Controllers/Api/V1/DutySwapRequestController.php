<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DutySwapRequestStoreRequest;
use App\Http\Resources\DutySwapRequestResource;
use App\Models\DutySwapRequest;
use App\Models\Group;
use App\Services\Groups\CreateDutySwapRequest;
use App\Services\Groups\RespondToDutySwapRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DutySwapRequestController extends Controller
{
    public function store(DutySwapRequestStoreRequest $request, Group $group, CreateDutySwapRequest $createSwapRequest): JsonResponse
    {
        $swapRequest = $createSwapRequest->handle($request->user(), $request->validated());

        return (new DutySwapRequestResource($swapRequest->load(['dutySlot.duty', 'requester', 'recipient'])))
            ->response()
            ->setStatusCode(201);
    }

    public function accept(Request $request, Group $group, DutySwapRequest $dutySwapRequest, RespondToDutySwapRequest $respondService): DutySwapRequestResource
    {
        abort_unless($dutySwapRequest->dutySlot->duty->group_id === $group->id, 404);
        abort_unless($dutySwapRequest->recipient_id === $request->user()->id, 403);
        abort_unless($dutySwapRequest->isPending(), 409);

        $respondService->accept($dutySwapRequest);

        return new DutySwapRequestResource($dutySwapRequest->load(['dutySlot.duty', 'requester', 'recipient']));
    }

    public function reject(Request $request, Group $group, DutySwapRequest $dutySwapRequest, RespondToDutySwapRequest $respondService): DutySwapRequestResource
    {
        abort_unless($dutySwapRequest->dutySlot->duty->group_id === $group->id, 404);
        abort_unless($dutySwapRequest->recipient_id === $request->user()->id, 403);
        abort_unless($dutySwapRequest->isPending(), 409);

        $respondService->reject($dutySwapRequest);

        return new DutySwapRequestResource($dutySwapRequest->load(['dutySlot.duty', 'requester', 'recipient']));
    }
}
