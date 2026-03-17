<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DutySlot extends Model
{
    protected $fillable = [
        'duty_id',
        'user_id',
        'date',
        'notified_day_before',
        'notified_same_day',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'notified_day_before' => 'boolean',
            'notified_same_day' => 'boolean',
        ];
    }

    public function duty(): BelongsTo
    {
        return $this->belongsTo(Duty::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
