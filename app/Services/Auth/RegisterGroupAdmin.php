<?php

namespace App\Services\Auth;

use App\Enums\GroupMemberRole;
use App\Enums\HomeDutyRole;
use App\Models\GroupInvitation;
use App\Models\User;
use App\Services\Groups\AcceptGroupInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisterGroupAdmin
{
    public function __construct(private readonly AcceptGroupInvitation $acceptGroupInvitation) {}

    /**
     * @param  array{name: string, email: string, phone_number: string, password: string, invitation_token?: string|null}  $attributes
     */
    public function handle(array $attributes): User
    {
        $email = Str::lower(trim($attributes['email']));
        $invitation = $this->resolveInvitation($attributes['invitation_token'] ?? null, $email);

        /** @var User $user */
        $user = DB::transaction(function () use ($attributes, $email, $invitation): User {
            $user = User::query()->create([
                'name' => trim($attributes['name']),
                'email' => $email,
                'phone_number' => $this->normalizePhoneNumber($attributes['phone_number']),
                'password' => $attributes['password'],
                'is_group_admin' => $invitation?->role === GroupMemberRole::Admin || is_null($invitation),
                // Invited users have already proven email ownership via the invitation link;
                // self-registered admins must verify via the Fortify Registered event email.
                'email_verified_at' => $invitation instanceof GroupInvitation ? now() : null,
            ]);

            if ($invitation instanceof GroupInvitation) {
                $this->acceptGroupInvitation->handle($invitation, $user);

                return $user;
            }

            $user->syncRoles(HomeDutyRole::GroupOwner);

            return $user;
        });

        return $user;
    }

    private function resolveInvitation(?string $token, string $email): ?GroupInvitation
    {
        if (blank($token)) {
            return null;
        }

        $invitation = GroupInvitation::query()
            ->pending()
            ->where('token', $token)
            ->first();

        if (! $invitation instanceof GroupInvitation) {
            throw ValidationException::withMessages([
                'email' => 'This invitation is no longer available. Request a new invite from your Home Group admin.',
            ]);
        }

        if (Str::lower($invitation->email) !== $email) {
            throw ValidationException::withMessages([
                'email' => 'This invitation was sent to a different email address.',
            ]);
        }

        return $invitation;
    }

    private function normalizePhoneNumber(string $phoneNumber): string
    {
        $trimmedPhoneNumber = trim($phoneNumber);

        if (str_starts_with($trimmedPhoneNumber, '+')) {
            $digits = preg_replace('/\D+/', '', Str::after($trimmedPhoneNumber, '+'));

            return '+'.$digits;
        }

        return preg_replace('/\D+/', '', $trimmedPhoneNumber) ?? $trimmedPhoneNumber;
    }
}
