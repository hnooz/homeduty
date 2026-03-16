<?php

namespace App\Models;

use App\Enums\DutyFrequency;
use Database\Factories\DutyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Duty extends Model
{
    /** @use HasFactory<DutyFactory> */
    use HasFactory;

    protected $fillable = [
        'group_id',
        'assigned_user_id',
        'name',
        'description',
        'frequency',
        'starts_on',
    ];

    protected static function newFactory(): DutyFactory
    {
        return DutyFactory::new();
    }

    protected function casts(): array
    {
        return [
            'frequency' => DutyFrequency::class,
            'starts_on' => 'date',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
