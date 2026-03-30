<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupStoreRequest;
use App\Models\Group;
use App\Services\Groups\CreateHomeGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function create(): Response
    {
        Gate::authorize('create', Group::class);

        return Inertia::render('groups/Create');
    }

    public function store(GroupStoreRequest $request, CreateHomeGroup $createHomeGroup): RedirectResponse
    {
        $group = $createHomeGroup->handle($request->user(), $request->validated());

        return to_route('dashboard')
            ->with('status', 'home-group-created')
            ->with('home_group_name', $group->name);
    }
}
