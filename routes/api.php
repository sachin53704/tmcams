<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
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


Route::prefix("auth")->group(function () {
    Route::post('/search', [AuthController::class, 'searchEmployeeCode']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});


Route::prefix('master')->group(function(){
    Route::get('departments', [MasterController::class, 'departments']);
    Route::get('designations', [MasterController::class, 'designations']);
    Route::get('classes', [MasterController::class, 'classes']);
    Route::get('wards', [MasterController::class, 'wards']);
    Route::get('sub-departments', [MasterController::class, 'subDepartments']);
});


Route::post('get-email', [UserController::class, 'getEmail']);
Route::post('set-random-password', [UserController::class, 'setRandomPassword']);

// Route::middleware('auth:sanctum')->group('/user', function (Request $request) {
//     return $request->user();
// });


Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::post('/profile', [UserController::class, 'updateProfile']);
    Route::post('/update-password', [UserController::class, 'updatePassword']);


    Route::get('/attendance', [AttendanceController::class, 'index']);
});
