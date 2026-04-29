<?php

namespace App\Http\Controllers;

use App\Http\Requests\DutySwapRequestStoreRequest;
use App\Models\DutySwapRequest;
use App\Models\Group;
use App\Services\Groups\CreateDutySwapRequest;
use App\Services\Groups\RespondToDutySwapRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DutySwapRequestController extends Controller
{
    public function store(DutySwapRequestStoreRequest $request, Group $group, CreateDutySwapRequest $createSwapRequest): RedirectResponse
    {
        $createSwapRequest->handle($request->user(), $request->validated());

        return to_route('groups.duties.index', $group)
            ->with('status', 'swap-request-sent');
    }

    public function accept(Request $request, Group $group, DutySwapRequest $dutySwapRequest, RespondToDutySwapRequest $respondService): RedirectResponse
    {
        abort_unless($dutySwapRequest->dutySlot->duty->group_id === $group->id, 404);
        abort_unless($dutySwapRequest->recipient_id === $request->user()->id, 403);
        abort_unless($dutySwapRequest->isPending(), 409);

        $respondService->accept($dutySwapRequest);

        return to_route('dashboard')
            ->with('status', 'swap-request-accepted');
    }

    public function reject(Request $request, Group $group, DutySwapRequest $dutySwapRequest, RespondToDutySwapRequest $respondService): RedirectResponse
    {
        abort_unless($dutySwapRequest->dutySlot->duty->group_id === $group->id, 404);
        abort_unless($dutySwapRequest->recipient_id === $request->user()->id, 403);
        abort_unless($dutySwapRequest->isPending(), 409);

        $respondService->reject($dutySwapRequest);

        return to_route('dashboard')
            ->with('status', 'swap-request-rejected');
    }
}
