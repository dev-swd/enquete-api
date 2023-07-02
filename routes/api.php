<?php

use App\Http\Controllers\EnqueteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', [HelloController::class, 'apiTest']);
Route::post('/test2', [HelloController::class, 'postTest']);

Route::post('/enquete-signin', [EnqueteController::class, 'enquete_signin']);
Route::post('/enquete-response', [EnqueteController::class, 'enquete_response'])->name('enquete.response');
Route::middleware('auth')->group(function () {
    Route::get('/template', [EnqueteController::class, 'template_index'])->name('template.index');
    Route::get('/enquete', [EnqueteController::class, 'enquete_index'])->name('enquete.index');
    Route::get('/client', [EnqueteController::class, 'client_index'])->name('client.index');
    Route::get('/request', [EnqueteController::class, 'request_index'])->name('request.index');
    Route::post('/template', [EnqueteController::class, 'template_create'])->name('template.create');
    Route::post('/enquete', [EnqueteController::class, 'enquete_create'])->name('enquete.create');
    Route::post('/client', [EnqueteController::class, 'client_create'])->name('client.create');
    Route::post('/request', [EnqueteController::class, 'request_create'])->name('request.create');
    Route::get('/response/{id}', [EnqueteController::class, 'response_show'])->name('response.show');
});