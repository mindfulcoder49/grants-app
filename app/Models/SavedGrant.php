<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'grant_info',
    ];

    protected $casts = [
        'grant_info' => 'array', // Cast JSON to array automatically
    ];
}
