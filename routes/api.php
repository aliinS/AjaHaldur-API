<?php

use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RefreshToken;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TableContentController;
use App\Http\Controllers\TableController;
use Illuminate\Support\Facades\Password;

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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/app/register', [AuthController::class, 'appRegister']);

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);

// api endpoitn to get servers time
Route::group([
    'middleware' => ['api', 'jwt.refresh'],
], function ($router) {
    $router->post('refresh', [RefreshToken::class, 'refresh']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('app/logout', [AuthController::class, 'appLogout']);
    
    Route::get('me', [AuthController::class, 'me']);

    Route::post('/user/avatar', [AuthController::class, 'updateAvatar']);
    Route::delete('/user/avatar', [AuthController::class, 'deleteAvatar']);

    Route::post('/user/update', [AuthController::class, 'update']);
    // TODO: make it useable
    // Route::post('/user/delete', [AuthController::class, 'delete']);

    Route::get('tables/personal', [TableController::class, 'index']);
    Route::post('/tables/store', [TableController::class, 'store']);
    Route::get('/tables/show/{id}', [TableController::class, 'show']);
    Route::get('/tables/hours/{id}', [TableController::class, 'hours']);
    Route::post('/tables/update/{id}', [TableController::class, 'update']);
    Route::delete('/tables/delete/{tableContent}', [TableController::class, 'destroy']);

    Route::get('/tables/content/show/{id}', [TableContentController::class, 'show']);
    Route::post('/tables/content/store', [TableContentController::class, 'store']);
    Route::post('/tables/content/update/{id}', [TableContentController::class, 'update']);
    Route::delete('/tables/content/delete/{id}', [TableContentController::class, 'destroy']);
    // table content filter endpoint
    Route::get('/tables/content/filter/{id}', [TableContentController::class, 'filter']);

    Route::get('groups', [GroupController::class, 'index']);
    Route::get('/groups/show/{id}', [GroupController::class, 'show']);
    Route::post('/groups/update/{group}', [GroupController::class, 'update']);
    Route::post('/groups/invite/{id}', [GroupController::class, 'invite']);
    Route::get('/groups/get-table/{id}', [GroupController::class, 'getTable']);
    // delete member from group
    Route::post('/groups/members/delete/{id}', [GroupController::class, 'deleteMember']);
    Route::post('/groups/store', [GroupController::class, 'store']);
    Route::post('/groups/delete/{id}', [GroupController::class, 'destroy']);

    Route::get('groups/{group_id}/shifts', [ShiftController::class, 'index']);
    Route::post('/groups/shifts/store', [ShiftController::class, 'store']);
    Route::post('/groups/shifts/{shift}/jobs', [ShiftController::class, 'storeJob']);
    Route::post('/groups/shifts/{shift}/staff', [ShiftController::class, 'storeStaff']);
    Route::get('/groups/shifts/{shift}/show', [ShiftController::class, 'show']);
    Route::post('/groups/shifts/{shift}/update', [ShiftController::class, 'update']);
    Route::post('/groups/shifts/{shift}/delete', [ShiftController::class, 'destroy']);

    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::get('/feedback/search', [FeedbackController::class, 'search']);
    Route::post('/feedback/store', [FeedbackController::class, 'store']);
    Route::get('/feedback/show/{feedback}', [FeedbackController::class, 'show']);
    Route::post('/feedback/update/{feedback}', [FeedbackController::class, 'update']);
    Route::post('/feedback/delete/{feedback}', [FeedbackController::class, 'destroy']);
});

Route::get('/data/system/time', [SystemController::class, 'time']);
