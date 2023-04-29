<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CounsellorController;
use App\Http\Controllers\PatientController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix'=>'patients'],function (){
    Route::post('login',[PatientController::class,'login']);
    Route::post('logout',[PatientController::class,'logout']);
});
Route::resource('patients',PatientController::class);

Route::group(['prefix'=>'counsellors'],function (){
    Route::post('login',[CounsellorController::class,'login']);
    Route::post('logout',[CounsellorController::class,'logout']);
});
Route::resource('counsellors',CounsellorController::class);
Route::resource('appointments',AppointmentController::class);
