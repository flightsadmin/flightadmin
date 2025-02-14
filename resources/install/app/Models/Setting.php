<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'airline_id',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function settingable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public function getTypedValueAttribute()
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'boolean' => (bool) $this->value,
            default => $this->value,
        };
    }
}
