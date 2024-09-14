<?php

// app/Http/Controllers/SiteInfoController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use DateTime;

class SiteInfoController extends Controller
{
    public function getLastUpdate()
    {
        // Check the cache first, fallback to database if necessary
        $lastUpdate = Cache::remember('last_update', 60, function () {
            // Get the latest 'updated_at' or 'created_at' from the 'grants' table
            $latestGrant = DB::table('grants')->orderBy('updated_at', 'desc')->first();
            $latestUpdate = $latestGrant->updated_at;
            //use DateTime to format the date like September 1, 2021 
            $latestUpdate = new DateTime($latestUpdate);
            $latestUpdate = $latestUpdate->format('F j, Y');

            return $latestUpdate;

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
