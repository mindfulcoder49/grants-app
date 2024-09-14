<?php

// app/Http/Controllers/SiteInfoController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SiteInfoController extends Controller
{
    public function getLastUpdate()
    {
        // Check the cache first, fallback to database if necessary
        $lastUpdate = Cache::remember('last_update', 60, function () {
            // Get the latest 'updated_at' or 'created_at' from the 'grants' table
            $latestGrant = DB::table('grants')->orderBy('updated_at', 'desc')->first();
            return $latestGrant ? $latestGrant->updated_at->format('M d, Y') : now()->format('M d, Y');
        });

        return response()->json([
            'lastUpdate' => $lastUpdate,
        ]);
    }

    public function update()
    {
        //display Update.vue
        return Inertia::render('Update');
    }
}
