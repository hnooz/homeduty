<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\RegisterGroupAdmin;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    public function __construct(private readonly RegisterGroupAdmin $registerGroupAdmin) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerGroupAdmin->handle($request->validated());

        $token = $user->createToken($request->validated('device_name'))->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => new UserResource($user),
            ],
        ], 201);
    }
}
