<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('duties', 'assigned_user_id')) {
            Schema::table('duties', function (Blueprint $table): void {
                $table->dropForeign(['assigned_user_id']);
                $table->dropColumn('assigned_user_id');
            });
        }

        if (Schema::hasColumn('duties', 'frequency')) {
            Schema::table('duties', function (Blueprint $table): void {
                $table->index('group_id', 'duties_group_id_temp_index');
            });

            Schema::table('duties', function (Blueprint $table): void {
                $table->dropIndex('duties_group_id_frequency_index');
                $table->dropColumn(['name', 'description', 'frequency']);
                $table->string('type', 32)->after('group_id');
                $table->index(['group_id', 'type']);
            });

            Schema::table('duties', function (Blueprint $table): void {
                $table->dropIndex('duties_group_id_temp_index');
            });
        }

        Schema::create('duty_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('duty_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order');
            $table->timestamps();

            $table->unique(['duty_id', 'user_id']);
            $table->index(['duty_id', 'sort_order']);
        });

        Schema::create('duty_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('duty_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('notified_day_before')->default(false);
            $table->boolean('notified_same_day')->default(false);
            $table->timestamps();

            $table->index(['duty_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_slots');
        Schema::dropIfExists('duty_members');

        Schema::table('duties', function (Blueprint $table): void {
            $table->dropIndex(['group_id', 'type']);
            $table->dropColumn('type');

            $table->foreignId('assigned_user_id')->nullable()->after('group_id')->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('frequency', 32);
            $table->index(['group_id', 'frequency']);
        });
    }
};
