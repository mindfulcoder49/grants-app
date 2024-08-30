<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AboutController extends Controller
{
    public function index() {
        // Render the About page
        return Inertia::render('About');
    }
}
