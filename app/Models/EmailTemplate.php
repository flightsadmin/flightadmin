<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'variables',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($template) {
            $template->slug = Str::slug($template->name);
        });
        static::updating(function ($template) {
            $template->slug = Str::slug($template->name);
        });
    }
}
