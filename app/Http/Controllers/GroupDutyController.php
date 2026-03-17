<?php

namespace App\Http\Controllers;

use App\Enums\DutyFrequency;
use App\Enums\GroupMemberRole;
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
            'duties.assignedUser',
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
                    'name' => $duty->name,
                    'description' => $duty->description,
                    'frequency' => $duty->frequency->value,
                    'frequencyLabel' => $duty->frequency->label(),
                    'startsOn' => $duty->starts_on?->toDateString(),
                    'assignedUser' => $duty->assignedUser
                        ? [
                            'id' => $duty->assignedUser->id,
                            'name' => $duty->assignedUser->name,
                            'email' => $duty->assignedUser->email,
                        ]
                        : null,
                ]),
            'assigneeOptions' => $group->memberships
                ->sortByDesc(fn (GroupMember $membership): bool => $membership->role === GroupMemberRole::Admin)
                ->values()
                ->map(fn (GroupMember $membership): array => [
                    'value' => $membership->user_id,
                    'label' => $membership->user->name,
                    'description' => $membership->role->label().' • '.$membership->user->email,
                ]),
            'frequencyOptions' => collect(DutyFrequency::cases())->map(fn (DutyFrequency $frequency): array => [
                'value' => $frequency->value,
                'label' => $frequency->label(),
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
