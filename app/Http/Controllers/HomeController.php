<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class HomeController extends Controller
{
    public function index() {
        // Load the Home page with initial data if needed
        return Inertia::render('Home');
    }
}
