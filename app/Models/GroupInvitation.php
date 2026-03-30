<?php

namespace App\Models;

use App\Enums\GroupMemberRole;
use Database\Factories\GroupInvitationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(GroupInvitationFactory::class)]
#[Fillable(['group_id', 'invited_by_user_id', 'accepted_by_user_id', 'name', 'email', 'phone_number', 'role', 'token', 'expires_at', 'accepted_at'])]
class GroupInvitation extends Model
{
    /** @use HasFactory<GroupInvitationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
            'role' => GroupMemberRole::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query
            ->whereNull('accepted_at')
            ->where(function (Builder $pendingQuery): void {
                $pendingQuery
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function isPending(): bool
    {
        return is_null($this->accepted_at) && (is_null($this->expires_at) || $this->expires_at->isFuture());
    }
}
