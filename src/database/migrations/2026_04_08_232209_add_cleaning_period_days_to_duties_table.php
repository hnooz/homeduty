<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('duties', function (Blueprint $table): void {
            $table->unsignedTinyInteger('cleaning_period_days')->nullable()->after('starts_on');
        });
    }

    public function down(): void
    {
        Schema::table('duties', function (Blueprint $table): void {
            $table->dropColumn('cleaning_period_days');
        });
    }
};
