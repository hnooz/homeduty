<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupInvitationResource;
use App\Http\Resources\GroupResource;
use App\Models\DutySlot;
use App\Models\Group;
use App\Models\GroupInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing(['ownedGroup', 'groupMemberships.group']);

        $activeGroup = $user->ownedGroup ?? $user->groupMemberships->first()?->group;

        $pendingInvitation = null;

        if (! $activeGroup) {
            $pendingInvitation = GroupInvitation::query()
                ->with('group')
                ->pending()
                ->where('email', $user->email)
                ->latest()
                ->first();
        }

        $activeGroup?->loadCount([
            'memberships as members_count',
            'invitations as pending_invitations_count' => fn ($query) => $query->pending(),
            'duties as duties_count',
        ]);

        $upcomingDuties = DutySlot::query()
            ->where('user_id', $user->id)
            ->where('date', '>=', now()->toDateString())
            ->with('duty')
            ->orderBy('date')
            ->limit(5)
            ->get()
            ->map(fn (DutySlot $slot): array => [
                'date' => $slot->date->toDateString(),
                'type' => $slot->duty->type->value,
                'type_label' => $slot->duty->type->label(),
                'type_icon' => $slot->duty->type->icon(),
            ])
            ->all();

        return response()->json([
            'data' => [
                'abilities' => [
                    'can_create_home_group' => $user->can('create', Group::class),
                    'can_manage_members' => $activeGroup ? $user->can('manageMembers', $activeGroup) : false,
                    'can_view_members' => $activeGroup ? $user->can('viewMembers', $activeGroup) : false,
                    'can_manage_duties' => $activeGroup ? $user->can('manageDuties', $activeGroup) : false,
                    'can_view_duties' => $activeGroup ? $user->can('viewDuties', $activeGroup) : false,
                ],
                'home_group' => $activeGroup
                    ? new GroupResource($activeGroup)
                    : null,
                'pending_invitation' => $pendingInvitation
                    ? new GroupInvitationResource($pendingInvitation)
                    : null,
                'upcoming_duties' => $upcomingDuties,
            ],
        ]);
    }
}
