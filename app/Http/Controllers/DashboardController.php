<?php

namespace App\Http\Controllers;

use App\Enums\DutySwapRequestStatus;
use App\Models\DutySlot;
use App\Models\DutySwapRequest;
use App\Models\Group;
use App\Models\GroupInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user()?->loadMissing(['ownedGroup', 'groupMemberships.group']);
        $activeGroup = $user?->ownedGroup ?? $user?->groupMemberships->first()?->group;
        $pendingInvitation = null;

        if ($user && ! $activeGroup) {
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

        $upcomingDuties = $user ? DutySlot::query()
            ->where('user_id', $user->id)
            ->where('date', '>=', now()->toDateString())
            ->with('duty')
            ->orderBy('date')
            ->limit(5)
            ->get()
            ->map(fn (DutySlot $slot) => [
                'date' => $slot->date->toDateString(),
                'type' => $slot->duty->type->label(),
                'icon' => $slot->duty->type->icon(),
            ]) : [];

        $incomingSwapRequests = $user ? DutySwapRequest::query()
            ->where('recipient_id', $user->id)
            ->where('status', DutySwapRequestStatus::Pending)
            ->with(['dutySlot.duty.group', 'requester'])
            ->get()
            ->map(fn (DutySwapRequest $req) => [
                'id' => $req->id,
                'groupId' => $req->dutySlot->duty->group_id,
                'groupName' => $req->dutySlot->duty->group->name,
                'requesterName' => $req->requester->name,
                'dutyType' => $req->dutySlot->duty->type->label(),
                'dutyIcon' => $req->dutySlot->duty->type->icon(),
                'date' => $req->dutySlot->date->toDateString(),
                'message' => $req->message,
                'createdAt' => $req->created_at?->toIso8601String(),
            ]) : [];

        $outgoingSwapRequests = $user ? DutySwapRequest::query()
            ->where('requester_id', $user->id)
            ->where('status', DutySwapRequestStatus::Pending)
            ->with(['dutySlot.duty', 'recipient'])
            ->get()
            ->map(fn (DutySwapRequest $req) => [
                'id' => $req->id,
                'recipientName' => $req->recipient->name,
                'dutyType' => $req->dutySlot->duty->type->label(),
                'dutyIcon' => $req->dutySlot->duty->type->icon(),
                'date' => $req->dutySlot->date->toDateString(),
                'createdAt' => $req->created_at?->toIso8601String(),
            ]) : [];

        return Inertia::render('Dashboard', [
            'canCreateHomeGroup' => $user?->can('create', Group::class) ?? false,
            'canManageHomeGroupMembers' => $activeGroup ? $user?->can('manageMembers', $activeGroup) ?? false : false,
            'canViewHomeGroupMembers' => $activeGroup ? $user?->can('viewMembers', $activeGroup) ?? false : false,
            'canManageHomeGroupDuties' => $activeGroup ? $user?->can('manageDuties', $activeGroup) ?? false : false,
            'canViewHomeGroupDuties' => $activeGroup ? $user?->can('viewDuties', $activeGroup) ?? false : false,
            'homeGroup' => $activeGroup
                ? [
                    'id' => $activeGroup->id,
                    'name' => $activeGroup->name,
                    'memberCount' => $activeGroup->members_count,
                    'pendingInvitationsCount' => $activeGroup->pending_invitations_count,
                    'dutiesCount' => $activeGroup->duties_count,
                    'isOwner' => $user?->ownedGroup?->is($activeGroup) ?? false,
                ]
                : null,
            'pendingInvitation' => $pendingInvitation
                ? [
                    'token' => $pendingInvitation->token,
                    'groupName' => $pendingInvitation->group->name,
                    'email' => $pendingInvitation->email,
                    'roleLabel' => $pendingInvitation->role->label(),
                    'expiresAt' => $pendingInvitation->expires_at?->toIso8601String(),
                ]
                : null,
            'upcomingDuties' => $upcomingDuties,
            'incomingSwapRequests' => $incomingSwapRequests,
            'outgoingSwapRequests' => $outgoingSwapRequests,
            'status' => $request->session()->get('status'),
            'homeGroupName' => $request->session()->get('home_group_name'),
        ]);
    }
}
