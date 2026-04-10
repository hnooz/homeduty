<?php

namespace Database\Seeders;

use App\Enums\HomeDutyRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@moidris.com',
        ]);

        $user->assignRole(HomeDutyRole::SuperAdmin->value);
    }
}
