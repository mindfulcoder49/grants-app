<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiteInfoController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\VectorController;

Route::post('/vector/insert', [VectorController::class, 'insertVector']);
Route::post('/vector/update/{id}', [VectorController::class, 'updateVector']);
Route::delete('/vector/delete/{id}', [VectorController::class, 'deleteVector']);
Route::post('/vector/cosine-similarity', [VectorController::class, 'calculateCosineSimilarity']);
Route::post('/vector/search', [VectorController::class, 'searchSimilarVectors']);
Route::post('/vector/embed', [VectorController::class, 'embedText']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


