<?php

use App\Enums\DutyType;
use App\Enums\HomeDutyRole;
use App\Models\Duty;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->createOne();
    $this->admin->assignRole(HomeDutyRole::SuperAdmin->value);
    actingAs($this->admin);
});

it('renders the group view page', function () {
    $group = Group::factory()->create(['owner_id' => $this->admin->id]);

    get(route('filament.admin.resources.groups.view', $group))
        ->assertOk();
});

it('renders the duty view page', function () {
    $group = Group::factory()->create(['owner_id' => $this->admin->id]);
    $duty = Duty::factory()->create([
        'group_id' => $group->id,
        'type' => DutyType::Cooking,
        'starts_on' => now()->toDateString(),
    ]);

    get(route('filament.admin.resources.duties.view', $duty))
        ->assertOk();
});
