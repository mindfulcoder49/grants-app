<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\GrantsController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\SiteInfoController;
use App\Http\Controllers\AiAssistantController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/', [GrantsController::class, 'search'])->name('search');
Route::post('/search', [GrantsController::class, 'search'])->name('search');
Route::get('/search', [GrantsController::class, 'search'])->name('search');
Route::get('/about', [AboutController::class, 'index'])->name('about');

Route::get('/api/last-update', [SiteInfoController::class, 'getLastUpdate']);
Route::post('/api/ai-chat', [AiAssistantController::class, 'handleRequest'])->name('ai.assistant');

/*
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
}); */

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
