<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RefreshToken;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TableContentController;
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
Route::group([
    'middleware' => ['api', 'jwt.refresh'],
], function ($router) {
    $router->post('refresh', [RefreshToken::class, 'refresh']);
});

Route::get('/data/system/time', [SystemController::class, 'time']);
Route::middleware('api')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('me', [AuthController::class, 'me']);

    Route::post('/user/update', [AuthController::class, 'update']);
    // TODO: make it useable
    // Route::post('/user/delete', [AuthController::class, 'delete']);

    Route::post('tables/personal', [TableController::class, 'index']);
    Route::post('/tables/store', [TableController::class, 'store']);
    Route::post('/tables/show/{id}', [TableController::class, 'show']);
    Route::post('/tables/hours/{id}', [TableController::class, 'hours']);
    Route::post('/tables/update/{id}', [TableController::class, 'update']);
    Route::post('/tables/delete/{id}', [TableController::class, 'destroy']);

    Route::post('/tables/content/store', [TableContentController::class, 'store']);
    Route::post('/tables/content/update/{id}', [TableContentController::class, 'update']);
    Route::post('/tables/content/delete/{id}', [TableContentController::class, 'destroy']);

    Route::post('groups', [GroupController::class, 'index']);
    Route::post('/groups/show/{id}', [GroupController::class, 'show']);
    Route::post('/groups/store', [GroupController::class, 'store']);
});
