<?php

use App\Enums\GroupMemberRole;
use App\Enums\HomeDutyRole;
use App\Models\User;
use App\Services\Roles\SyncHomeDutyAuthorization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        app(SyncHomeDutyAuthorization::class)->handle();

        User::query()
            ->with(['ownedGroup', 'groupMemberships'])
            ->lazyById()
            ->each(function (User $user): void {
                $resolvedRole = null;

                if ($user->ownedGroup()->exists() || ($user->is_group_admin && ! $user->groupMemberships()->exists())) {
                    $resolvedRole = HomeDutyRole::GroupOwner;
                } elseif ($user->groupMemberships()->where('role', GroupMemberRole::Admin->value)->exists()) {
                    $resolvedRole = HomeDutyRole::GroupAdmin;
                } elseif ($user->groupMemberships()->exists()) {
                    $resolvedRole = HomeDutyRole::GroupMember;
                }

                $user->syncRoles($resolvedRole ? [$resolvedRole] : []);
                $user->forceFill([
                    'is_group_admin' => $resolvedRole === HomeDutyRole::GroupAdmin,
                ])->saveQuietly();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::query()->lazyById()->each(function (User $user): void {
            $user->syncRoles([]);
        });
    }
};
