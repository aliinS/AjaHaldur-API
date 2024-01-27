<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TableController;

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

Route::get('/test', function () {
    return response()->json([
        'data' => [
            '1' => 1,
            '2' => 2,
            '3' => 1,
            '4' => 1,
            '5' => 1,
            '6' => 1,
        ]
    ]);
});

// Route::post('/register', [AuthController::class, 'register']);

// // Login an existing user
// Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

// api endpoitn to get servers time

Route::get('/data/system/time', [SystemController::class, 'time']);
Route::middleware('api')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    
    Route::post('tables/personal', [TableController::class, 'index']);
    Route::post('/tables/store', [TableController::class, 'store']);
    Route::post('/tables/show/{id}', [TableController::class, 'show']);

    Route::post('tables/groups', [GroupController::class, 'index']);
});
