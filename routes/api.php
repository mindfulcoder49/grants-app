<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteInfoController;
use App\Http\Controllers\AiAssistantController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/last-update', [SiteInfoController::class, 'getLastUpdate']);
Route::post('/ai-chat', [AiAssistantController::class, 'handleRequest'])->name('ai.assistant');