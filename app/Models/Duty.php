<?php

namespace App\Models;

use App\Enums\DutyType;
use Database\Factories\DutyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Duty extends Model
{
    /** @use HasFactory<DutyFactory> */
    use HasFactory;

    protected $fillable = [
        'group_id',
        'type',
        'starts_on',
    ];

    protected static function newFactory(): DutyFactory
    {
        return DutyFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DutyType::class,
            'starts_on' => 'date',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'duty_members')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->withTimestamps();
    }

    public function slots(): HasMany
    {
        return $this->hasMany(DutySlot::class);
    }
}
