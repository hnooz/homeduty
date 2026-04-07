<?php

namespace App\Models;

use App\Enums\HomeDutyRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[UseFactory(UserFactory::class)]
#[Fillable(['name', 'email', 'phone_number', 'is_group_admin', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Impersonate, Notifiable, TwoFactorAuthenticatable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(HomeDutyRole::SuperAdmin->value);
    }

    public function canImpersonate(): bool
    {
        return $this->hasRole(HomeDutyRole::SuperAdmin->value);
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole(HomeDutyRole::SuperAdmin->value);
    }

    public function ownedGroup(): HasOne
    {
        return $this->hasOne(Group::class, 'owner_id');
    }

    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function assignedDuties(): BelongsToMany
    {
        return $this->belongsToMany(Duty::class, 'duty_members')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function dutySlots(): HasMany
    {
        return $this->hasMany(DutySlot::class);
    }

    public function hasHomeGroupAdminRole(): bool
    {
        return $this->hasRole(HomeDutyRole::GroupAdmin);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_group_admin' => 'boolean',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
