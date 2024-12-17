<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ETLController;
use App\Http\Controllers\SeederController;
use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
    Route::post('refresh', 'refresh');
});

Route::get('/statistics/hourly', [ETLController::class, 'getHourlyStatistics']);
Route::get('/statistics/historical', [ETLController::class, 'getHistoricalStatistics']);
Route::get('/streams', [StreamController::class, 'getAllStreams']);

Route::group(["middleware" => 'auth:api'], function() {
    Route::get('/migrate-fresh-seed', [SeederController::class, 'migrateFreshAndSeed']);
});
