<?php

use App\Enums\HomeDutyRole;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('allows super admins to update a user password from the admin users edit page', function (): void {
    /** @var User $admin */
    $admin = User::factory()->createOne();
    $admin->assignRole(HomeDutyRole::SuperAdmin->value);

    /** @var User $member */
    $member = User::factory()->createOne([
        'password' => 'OldPass!1234',
    ]);

    actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $member->getRouteKey()])
        ->set('data.password', 'NewPass!12345')
        ->set('data.password_confirmation', 'NewPass!12345')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('NewPass!12345', $member->fresh()->password))->toBeTrue();
});
