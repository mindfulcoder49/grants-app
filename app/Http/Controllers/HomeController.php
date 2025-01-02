<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Grant;

class HomeController extends Controller
{
    public function index(Request $request) 
    {
        $grants = [];

        // Check if the 'grants' parameter is present in the query string
        if ($request->has('grants')) {
            // Parse the 'grants' parameter into an array
            $grantIds = explode(',', $request->query('grants'));

            // Fetch the grants matching the provided opportunity IDs
            $grants = Grant::whereIn('opportunity_id', $grantIds)->get();
        }

        // Load the Home page with the grants and other data
        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'grants' => $grants,
        ]);
    }
}
