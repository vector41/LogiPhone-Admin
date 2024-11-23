<?php

use App\Http\Controllers\API\AppDataController;
use App\Http\Controllers\API\Auth\APIAuthenticatedSessionController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\CallController;
use App\Http\Controllers\API\SmsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [APIAuthenticatedSessionController::class, 'store']);

Route::get('/company/all', [SupplierController::class, 'list']);
Route::get('/employees/all', [EmployeeController::class, 'list']);

Route::get('/favorite/all', [FavoriteController::class, 'list']);
Route::post('/favorite/add', [FavoriteController::class, 'save']);

Route::get('/appdata/all', [AppDataController::class, 'list']);
Route::get('/appdata/detail', [AppDataController::class, 'get']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [APIAuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});


// app user controller
Route::get('/app/getAllUsers', [SupplierController::class, 'getAllUsers']);

// app call controller
/*
param:
    {
        callNumber,receiveNumber
    }

res:{
    sender_id, receiver_id, created_at
}
*/
Route::post('/app/insertCall', [CallController::class, 'insertCall']);

/*
param:
    {
        callID,receiveID,date
    }

res:[]
*/
Route::post('/app/endCall', [CallController::class, 'endCall']);
// Route::post('/app/searchCall',[CallController::class,'searchCall']);


// sms controller

/*
param:
    {
        callNumber,receiveNumber
    }
*/

Route::post('/app/insertSms', [SmsController::class, 'insertSms']);

// Route::get('/app/searchSms',[SmsController::class,'searchSms']);

// server info
// makoto@tejima.jp
// test



//// ***** android app codes ********** ////

// Android get all user list
Route::get('/getAllUsersByPage', [EmployeeController::class, 'getAllUsersByPage']);
// get user details
Route::get('/getSpecificUser?keyword={keyword}', [EmployeeController::class, 'getSpecificUser']);
