<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    protected $fillable = ['key', 'value'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['key', 'value'])
            ->logOnlyDirty();
    }

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
