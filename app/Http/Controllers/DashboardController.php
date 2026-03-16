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
        $user = $request->user()?->loadMissing('ownedGroup');

        return Inertia::render('Dashboard', [
            'canCreateHomeGroup' => $user?->can('create', Group::class) ?? false,
            'homeGroup' => $user?->ownedGroup
                ? [
                    'id' => $user->ownedGroup->id,
                    'name' => $user->ownedGroup->name,
                ]
                : null,
            'status' => $request->session()->get('status'),
            'homeGroupName' => $request->session()->get('home_group_name'),
        ]);
    }
}