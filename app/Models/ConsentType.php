<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentType extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'description',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];
}
