<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

class HomeController extends Controller
{
    public function index() {
        // Load the Home page with initial data if needed
        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }
}
