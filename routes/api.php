<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PharmaController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ConsumerController;


use App\Http\Controllers\delivaryController;
use App\Http\Controllers\PharmacistController;
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


Route::get('/getalldelivaries', [AdminController::class, 'getalldelivaries']);
Route::get('/getallusers', [AdminController::class, 'getallusers']);

Route::get('/getallpharmas', [AdminController::class, 'getallpharmas']);

});

Route::middleware(['auth:api', 'consumer'])->group(function () {
Route::get('/ShowProductsOfCategoryc/{pharma_id}/{category_id}', [ConsumerController::class, 'ShowProductsOfCategoryc']);//pharma,category
Route::get('/Allcategoriesc', [ConsumerController::class, 'Allcategoriesc']);
Route::post('/AddToCart', [ConsumerController::class, 'AddToCart']);
Route::post('/AddOnewithoutaddtocart', [ConsumerController::class, 'AddOnewithoutaddtocart']);
Route::post('/MinusOnewithoutaddtocart', [ConsumerController::class, 'MinusOnewithoutaddtocart']);
Route::post('/EditCartAddOne/{id}', [ConsumerController::class, 'EditCartAddOne']);
Route::post('/EditCartMinusOne/{id}', [ConsumerController::class, 'EditCartMinusOne']);

Route::post('/public_order',[OrdersController::class,'Order']);
Route::post('/OrderPrivate',[OrdersController::class,'OrderPrivate']);
Route::post('/showQrFromDatabase', [QrCodeController::class, 'showQrFromDatabase']);

Route::get('get_order_price', [OrdersController::class,'index']);
Route::get('getAllArticles', [ConsumerController::class,'getAllArticles']);
Route::post('addlike/{id}', [ConsumerController::class,'addlike']);
Route::post('adddislike/{id}', [ConsumerController::class,'adddislike']);
Route::post('removelike/{id}', [ConsumerController::class,'removelike']);
Route::post('removedislike/{id}', [ConsumerController::class,'removedislike']);
// Route::post('evaluateproduct', [ConsumerController::class,'evaluateproduct']);
Route::post('addrecommendation', [ConsumerController::class,'addrecommendation']);
Route::get('showRecommendationOfProduct/{id}', [ConsumerController::class,'showRecommendationOfProduct']);
Route::post('deleteRecommendation/{id}', [ConsumerController::class,'deleteRecommendation']);
Route::post('confirmcartorder', [ConsumerController::class,'confirmcartorder']);
Route::get('show_qr_ofcartorderwithdetail/{id}', [ConsumerController::class,'show_qr_ofcartorderwithdetail']);
Route::get('getPharmacists', [PharmaController::class, 'getPharmacists']);
Route::get('getproductofcart/{id}', [ConsumerController::class,'getproductofcart']);//pharma_id
Route::get('getordersforconsumer/{id}', [ConsumerController::class,'getordersforconsumer']);//pharma_id
Route::get('cartorderarchive/{id}', [ConsumerController::class,'cartorderarchive']);//pharma_id
Route::post('acceptOrderc', [OrdersController::class,'acceptOrderc']);



Route::post('refuseOrderc', [OrdersController::class,'refuseOrderc']);


Route::post('/getConsumerPendingRequests', [delivaryController::class, 'getConsumerPendingRequests']);

Route::post('/applyPointDiscountByOrder', [OrdersController::class, 'applyPointDiscount']);

Route::post('profile', [ConsumerController::class,'profile']);

Route::post('updatePhoto', [ConsumerController::class,'updatePhoto']);

Route::post('search', [PharmaController::class, 'search']);

Route::post('store', [PharmaController::class, 'store']);
    

});

Route::middleware(['auth:api', 'pharmacist'])->group(function () {
Route::post('getAvailablePublicOrders', [PharmaController::class, 'getAvailablePublicOrders']);
Route::post('getAvailablePrivateOrders', [PharmaController::class, 'getAvailablePrivateOrders']);
Route::post('acceptOrder', [PharmaController::class, 'acceptOrder']);

Route::post('refuseOrder', [PharmaController::class, 'refuseOrder']);
  Route::post('/Addproduct', [PharmacistController::class, 'Addproduct']);
  Route::get('/Allcategories', [PharmacistController::class, 'Allcategories']);
  Route::get('/ShowProductsOfCategoryph/{category_id}', [PharmacistController::class, 'ShowProductsOfCategoryph']);//pharma,category
  Route::post('/addarticel', [PharmacistController::class, 'addarticel']);
  Route::get('/showmyarticles', [PharmacistController::class, 'showmyarticles']);
  Route::post('/deletearticle/{id}', [PharmacistController::class, 'deletearticle']);
  Route::post('/edittopic', [PharmacistController::class, 'edittopic']);
  Route::post('/editcontent', [PharmacistController::class, 'editcontent']);

  Route::post('acceptRecommendation', [PharmaController::class, 'acceptRecommendation']);
Route::post('refuseRecommendation', [PharmaController::class, 'refuseRecommendation']);

  Route::get('/getallcartorderforpharmacist', [PharmacistController::class, 'getallcartorderforpharmacist']);
  Route::post('/acceptcartorder/{id}', [PharmacistController::class, 'acceptcartorder']);
  Route::post('/editproduct', [PharmacistController::class, 'editproduct']);
  Route::post('/edittype', [PharmacistController::class, 'edittype']);


});

Route::middleware(['auth:api', 'delivery'])->group(function () {
Route::post('/generate-qr', [QrCodeController::class, 'generate']);
Route::post('/verifyQr', [QrCodeController::class, 'verifyQr']);
Route::post('/getPendingRequests', [delivaryController::class, 'getPendingRequests']);
Route::get('/getcartordertodelivery', [delivaryController::class, 'getcartordertodelivery']);
Route::post('/applycartorder/{id}', [delivaryController::class, 'applycartorder']);
Route::post('/verifyqrforcartorder', [delivaryController::class, 'verifyqrforcartorder']);

});



