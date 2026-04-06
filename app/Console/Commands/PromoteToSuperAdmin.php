<?php

namespace App\Console\Commands;

use App\Enums\HomeDutyRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('homeduty:promote-admin {email : The email of the user to promote}')]
#[Description('Promote a user to Super Admin')]
class PromoteToSuperAdmin extends Command
{
    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("User with email [{$this->argument('email')}] not found.");

            return self::FAILURE;
        }

        if ($user->hasRole(HomeDutyRole::SuperAdmin->value)) {
            $this->warn("User [{$user->email}] is already a Super Admin.");

            return self::SUCCESS;
        }

        $user->assignRole(HomeDutyRole::SuperAdmin->value);

        $this->info("User [{$user->email}] has been promoted to Super Admin.");

        return self::SUCCESS;
    }
}
