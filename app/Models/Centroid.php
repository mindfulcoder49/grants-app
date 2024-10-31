<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Centroid extends Model
{
    protected $fillable = ['vector'];

    // If storing vector as JSON, you can cast it to an array
    protected $casts = [
        'vector' => 'array',
    ];
}
