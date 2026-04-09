<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_phone_number_unique');
            $table->dropColumn('phone_number');
        });

        Schema::table('group_invitations', function (Blueprint $table): void {
            $table->dropColumn('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone_number', 25)->nullable()->unique()->after('email');
        });

        Schema::table('group_invitations', function (Blueprint $table): void {
            $table->string('phone_number')->nullable()->after('email');
        });
    }
};
