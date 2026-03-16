<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Str;

class RegisterGroupAdmin
{
    /**
     * @param  array{name: string, email: string, phone_number: string, password: string}  $attributes
     */
    public function handle(array $attributes): User
    {
        return User::query()->create([
            'name' => trim($attributes['name']),
            'email' => Str::lower(trim($attributes['email'])),
            'phone_number' => $this->normalizePhoneNumber($attributes['phone_number']),
            'password' => $attributes['password'],
            'is_group_admin' => true,
        ]);
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