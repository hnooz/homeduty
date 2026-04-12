<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('sends a password reset link', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    postJson(route('api.v1.auth.forgot-password'), [
        'email' => $user->email,
    ])->assertOk();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets the password with a valid token', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    postJson(route('api.v1.auth.forgot-password'), [
        'email' => $user->email,
    ])->assertOk();

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
        $token = $notification->token;

        return true;
    });

    postJson(route('api.v1.auth.reset-password'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertOk();

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
});

it('rejects a reset with an invalid token', function (): void {
    $user = User::factory()->create();

    postJson(route('api.v1.auth.reset-password'), [
        'token' => 'invalid',
        'email' => $user->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});
