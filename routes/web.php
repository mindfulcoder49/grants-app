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
use App\Http\Controllers\SavedGrantController;
use App\Http\Controllers\EmailController;


Route::post('/feedback', [EmailController::class, 'store']);

Route::middleware('auth')->group(function () {
    Route::post('/saved-grants', [SavedGrantController::class, 'store']);
    Route::get('/saved-grants', [SavedGrantController::class, 'index'])->name('saved-grants');
    Route::delete('/saved-grants/{id}', [SavedGrantController::class, 'destroy']);
});


Route::get('/vector-test', function () {
    return Inertia::render('VectorTest');
})->name('vector.test');

Route::get('/search-test', function () {
    return Inertia::render('SearchTest');
})->name('search.test');

Route::get('/centroid-test', function () {
    return Inertia::render('SearchCentroidTest');
})->name('centroid.test');

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/', [GrantsController::class, 'search'])->name('search');
Route::post('/search', [GrantsController::class, 'search'])->name('search');
Route::get('/search', [GrantsController::class, 'search'])->name('search');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/update', [SiteInfoController::class, 'update'])->name('update');

Route::get('/api/last-update', [SiteInfoController::class, 'getLastUpdate']);
Route::post('/api/ai-chat', [AiAssistantController::class, 'handleRequest'])->name('ai.assistant');
Route::get('/api/search-fields', [GrantsController::class, 'getSearchFields'])->name('search.fields');

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
    Route::post('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.updateSettings');
});

Route::post('/dispatch-saved-alerts', [EmailController::class, 'dispatchSavedAlertsJob'])->middleware('auth');


require __DIR__.'/auth.php';
