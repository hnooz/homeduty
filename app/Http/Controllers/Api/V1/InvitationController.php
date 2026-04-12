<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupInvitationResource;
use App\Models\GroupInvitation;
use App\Services\Groups\AcceptGroupInvitation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvitationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $invitations = GroupInvitation::query()
            ->with(['group', 'invitedBy'])
            ->pending()
            ->where('email', strtolower($request->user()->email))
            ->latest()
            ->get();

        return GroupInvitationResource::collection($invitations);
    }

    public function show(Request $request, GroupInvitation $invitation): GroupInvitationResource
    {
        abort_unless($invitation->isPending(), 404);
        abort_unless(strtolower($request->user()->email) === strtolower($invitation->email), 403);

        return new GroupInvitationResource($invitation->load(['group', 'invitedBy']));
    }

    public function accept(Request $request, GroupInvitation $invitation, AcceptGroupInvitation $acceptGroupInvitation): JsonResponse
    {
        abort_unless($invitation->isPending(), 404);
        abort_unless(strtolower($request->user()->email) === strtolower($invitation->email), 403);

        $acceptGroupInvitation->handle($invitation, $request->user());

        return response()->json([
            'message' => 'Invitation accepted.',
            'data' => new GroupInvitationResource($invitation->fresh()->load('group')),
        ]);
    }

    public function decline(Request $request, GroupInvitation $invitation): JsonResponse
    {
        abort_unless($invitation->isPending(), 404);
        abort_unless(strtolower($request->user()->email) === strtolower($invitation->email), 403);

        $invitation->delete();

        return response()->json([
            'message' => 'Invitation declined.',
        ]);
    }
}
