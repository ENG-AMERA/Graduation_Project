<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::group([

//     'middleware' => 'auth:api',
//     //'prefix' => 'auth/user'

// ], function ($router) {
//     Route::post('/register', [UserController::class, 'register'])->name('register');
//     Route::post('/login', [UserController::class, 'login'])->name('login');
//     Route::post('/logout', [UserController::class, 'logout'])->name('logout');
//     Route::post('/refresh', [UserController::class, 'refresh'])->name('refresh');
//     Route::post('/me', [UserController::class, 'me'])->name('me');
// });


Route::group([
      'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/refresh', [UserController::class, 'refresh']);
    Route::post('/me', [UserController::class, 'me']);

});


Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::post('/ping', function () {
        return response()->json(['pong' => true]);
    });
});

Route::middleware(['auth:api', 'consumer'])->group(function () {

});

Route::middleware(['auth:api', 'pharmacist'])->group(function () {

});

Route::middleware(['auth:api', 'delivery'])->group(function () {


});
