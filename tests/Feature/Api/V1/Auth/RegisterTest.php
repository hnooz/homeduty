<?php

use App\Enums\HomeDutyRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('registers a new home group admin and returns a token', function (): void {
    postJson(route('api.v1.auth.register'), [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
        'device_name' => 'Pixel 8',
    ])
        ->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'token',
                'user' => ['id', 'name', 'email'],
            ],
        ])
        ->assertJsonPath('data.user.email', 'ada@example.com');

    $user = User::query()->where('email', 'ada@example.com')->firstOrFail();

    expect($user->hasRole(HomeDutyRole::GroupOwner->value))->toBeTrue();
    expect($user->tokens()->where('name', 'Pixel 8')->exists())->toBeTrue();
});

it('rejects a duplicate email', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    postJson(route('api.v1.auth.register'), [
        'name' => 'Another Ada',
        'email' => 'taken@example.com',
        'password' => 'password1234',
        'password_confirmation' => 'password1234',
        'device_name' => 'Pixel 8',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('requires name, email, password and device name', function (): void {
    postJson(route('api.v1.auth.register'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password', 'device_name']);
});
