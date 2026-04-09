<?php

use App\Models\Group;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table): void {
            $table->uuid('invite_token')->nullable()->unique()->after('owner_id');
        });

        Group::query()->whereNull('invite_token')->get()->each(function (Group $group): void {
            $group->forceFill(['invite_token' => (string) Str::uuid()])->save();
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table): void {
            $table->dropUnique(['invite_token']);
            $table->dropColumn('invite_token');
        });
    }
};
