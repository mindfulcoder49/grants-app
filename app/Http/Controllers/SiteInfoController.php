<?php

// app/Http/Controllers/SiteInfoController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteInfoController extends Controller
{
    public function getLastUpdate()
    {
        Cache::put('last_update', now()->format('M d, Y'), 60); // Store last update date for 60 minutes
        // Example: Retrieve last update date from cache or database
        $lastUpdate = Cache::get('last_update', now()->format('M d, Y')); // Default to current date if not set

        return response()->json([
            'lastUpdate' => $lastUpdate,
        ]);
    }
}
