<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('issues a sanctum token for valid credentials', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
    ]);

    postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'secret-password',
        'device_name' => 'iPhone 15',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'token',
                'user' => ['id', 'name', 'email'],
            ],
        ])
        ->assertJsonPath('data.user.id', $user->id);

    expect($user->tokens()->where('name', 'iPhone 15')->exists())->toBeTrue();
});

it('rejects invalid credentials', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('secret-password'),
    ]);

    postJson(route('api.v1.auth.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'iPhone 15',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('requires email, password and device name', function (): void {
    postJson(route('api.v1.auth.login'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password', 'device_name']);
});
