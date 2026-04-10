<?php

use App\Enums\GroupMemberRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('group_members', function (Blueprint $table) {
            $table->string('role_name')->default(GroupMemberRole::Member->value)->after('user_id');
        });

        DB::table('group_members')->update([
            'role_name' => DB::raw('role'),
        ]);

        Schema::table('group_members', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('group_members', function (Blueprint $table) {
            $table->renameColumn('role_name', 'role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('group_members', function (Blueprint $table) {
            $table->enum('role_enum', [
                GroupMemberRole::Admin->value,
                GroupMemberRole::Member->value,
            ])->default(GroupMemberRole::Member->value)->after('user_id');
        });

        DB::table('group_members')->update([
            'role_enum' => DB::raw('role'),
        ]);

        Schema::table('group_members', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('group_members', function (Blueprint $table) {
            $table->renameColumn('role_enum', 'role');
        });
    }
};
