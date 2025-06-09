<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PharmaController;
use App\Http\Controllers\ConsumerController;
use App\Http\Controllers\delivaryController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PharmacistController;


use App\Http\Controllers\QrCodeController;
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

Route::group([
    'prefix' => 'password'
], function ($router) {
  Route::post('/userforgotpassword', [UserController::class, 'userforgotpassword']);
  Route::post('/userCheckcode', [UserController::class, 'userCheckcode']);
  Route::post('/userResetPassword', [UserController::class, 'userResetPassword']);
});

Route::group([
      'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/refresh', [UserController::class, 'refresh']);
    Route::post('/me', [UserController::class, 'me']);
    Route::post('/verify-email-code', [UserController::class, 'verifyEmailCode']);
    Route::post('/resendVerificationCode', [UserController::class, 'resendVerificationCode']);
    Route::post('/pharma_request', [PharmaController::class, 'pharma_request']);
    Route::post('/delivery_request', [delivaryController::class, 'delivery_request']);

});

Route::middleware(['auth:api', 'admin'])->group(function () {

Route::get('/getPendingPharmacists', [PharmaController::class, 'getPendingPharmacists']);
Route::get('/getPendingdelivery', [delivaryController::class, 'getPendingDelivery']);

Route::post('/accept_pharma', [PharmaController::class, 'accept']);
Route::delete('/pharmacist/{id}', [PharmaController::class, 'deletePharmacist']);

Route::post('/accept_delivary', [delivaryController::class, 'accept']);
Route::delete('/delivary/{id}', [delivaryController::class, 'deletdelivery']);

});

Route::middleware(['auth:api', 'consumer'])->group(function () {
Route::get('/ShowProductsOfCategory/{pharma_id}/{category_id}', [ConsumerController::class, 'ShowProductsOfCategory']);//pharma,category
Route::get('/Allcategories', [ConsumerController::class, 'Allcategories']);
Route::post('/AddToCart', [ConsumerController::class, 'AddToCart']);
Route::post('/AddOnewithoutaddtocart', [ConsumerController::class, 'AddOnewithoutaddtocart']);
Route::post('/MinusOnewithoutaddtocart', [ConsumerController::class, 'MinusOnewithoutaddtocart']);
Route::post('/EditCartAddOne/{id}', [ConsumerController::class, 'EditCartAddOne']);
Route::post('/EditCartMinusOne/{id}', [ConsumerController::class, 'EditCartMinusOne']);

Route::post('/public_order',[OrdersController::class,'Order']);
Route::post('/OrderPrivate',[OrdersController::class,'OrderPrivate']);
Route::post('/showQrFromDatabase', [QrCodeController::class, 'showQrFromDatabase']);

Route::get('get_order_price', [OrdersController::class, 'index']);

});

Route::middleware(['auth:api', 'pharmacist'])->group(function () {
Route::post('getAvailablePublicOrders', [PharmaController::class, 'getAvailablePublicOrders']);
Route::post('getAvailablePrivateOrders', [PharmaController::class, 'getAvailablePrivateOrders']);
Route::post('acceptOrder', [PharmaController::class, 'acceptOrder']);

Route::post('refuseOrder', [PharmaController::class, 'refuseOrder']);

  Route::post('/Addproduct', [PharmacistController::class, 'Addproduct']);
  Route::get('/Allcategories', [PharmacistController::class, 'Allcategories']);
  Route::get('/ShowProductsOfCategory/{pharma_id}/{category_id}', [PharmacistController::class, 'ShowProductsOfCategory']);//pharma,category


});

Route::middleware(['auth:api', 'delivery'])->group(function () {

Route::post('/generate-qr', [QrCodeController::class, 'generate']);

Route::post('/verifyQr', [QrCodeController::class, 'verifyQr']);


Route::post('/getPendingRequests', [delivaryController::class, 'getPendingRequests']);


});


//show order to delivery when user and pharma accept
//accept user on price
//make three layer for qr
