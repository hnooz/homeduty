<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user()?->loadMissing(['ownedGroup', 'groupMemberships.group']);
        $activeGroup = $user?->ownedGroup ?? $user?->groupMemberships->first()?->group;

        $activeGroup?->loadCount([
            'memberships as members_count',
            'invitations as pending_invitations_count' => fn ($query) => $query->pending(),
            'duties as duties_count',
        ]);

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
            'status' => $request->session()->get('status'),
            'homeGroupName' => $request->session()->get('home_group_name'),
        ]);
    }
}
