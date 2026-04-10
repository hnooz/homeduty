<?php

namespace App\Http\Controllers;

use App\Enums\DutyType;
use App\Http\Requests\GroupDutyStoreRequest;
use App\Http\Requests\GroupDutyUpdateRequest;
use App\Models\Duty;
use App\Models\Group;
use App\Models\GroupMember;
use App\Services\Groups\CreateGroupDuty;
use App\Services\Groups\RemoveGroupDuty;
use App\Services\Groups\UpdateGroupDuty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GroupDutyController extends Controller
{
    public function index(Request $request, Group $group): Response
    {
        Gate::authorize('viewDuties', $group);

        $group->load([
            'duties.members',
            'duties.slots.user',
            'memberships.user',
        ]);

        return Inertia::render('groups/Duties', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
            ],
            'duties' => $group->duties
                ->sortBy('starts_on')
                ->values()
                ->map(fn (Duty $duty): array => [
                    'id' => $duty->id,
                    'type' => $duty->type->value,
                    'typeLabel' => $duty->type->label(),
                    'typeIcon' => $duty->type->icon(),
                    'startsOn' => $duty->starts_on?->toDateString(),
                    'cleaningPeriodDays' => $duty->cleaning_period_days,
                    'members' => $duty->members->map(fn ($user): array => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ])->values()->all(),
                    'upcomingSlots' => $duty->slots
                        ->where('date', '>=', now()->startOfDay())
                        ->take(8)
                        ->map(fn ($slot): array => [
                            'date' => $slot->date->toDateString(),
                            'userName' => $slot->user?->name ?? 'Unassigned',
                            'userId' => $slot->user_id,
                        ])->values()->all(),
                ]),
            'memberOptions' => $group->memberships
                ->values()
                ->map(fn (GroupMember $membership): array => [
                    'value' => $membership->user_id,
                    'label' => $membership->user->name,
                ]),
            'typeOptions' => collect(DutyType::cases())->map(fn (DutyType $type): array => [
                'value' => $type->value,
                'label' => $type->label(),
                'icon' => $type->icon(),
            ]),
            'canManageDuties' => $request->user()->can('manageDuties', $group),
            'status' => session('status'),
        ]);
    }

    public function store(GroupDutyStoreRequest $request, Group $group, CreateGroupDuty $createGroupDuty): RedirectResponse
    {
        $createGroupDuty->handle($group, $request->validated());

        return to_route('groups.duties.index', $group)
            ->with('status', 'duty-created');
    }

    public function update(GroupDutyUpdateRequest $request, Group $group, Duty $duty, UpdateGroupDuty $updateGroupDuty): RedirectResponse
    {
        Gate::authorize('manageDuties', $group);

        abort_unless($duty->group_id === $group->id, 404);

        $updateGroupDuty->handle($duty, $request->validated());

        return to_route('groups.duties.index', $group)
            ->with('status', 'duty-updated');
    }

    public function destroy(Group $group, Duty $duty, RemoveGroupDuty $removeGroupDuty): RedirectResponse
    {
        Gate::authorize('manageDuties', $group);

        abort_unless($duty->group_id === $group->id, 404);

        $removeGroupDuty->handle($duty);

        return to_route('groups.duties.index', $group)
            ->with('status', 'duty-removed');
    }
}
