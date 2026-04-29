<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load('roles'));
    }

    public function update(UserUpdateRequest $request): UserResource
    {
        $user = $request->user();

        $user->fill($request->validated())->save();

        return new UserResource($user->fresh()->load('roles'));
    }
}
