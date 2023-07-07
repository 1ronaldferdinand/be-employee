<?php

use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\PositionController;
use App\Models\EmployeeModel;
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

Route::get('/employees', [EmployeeController::class, 'index']);
Route::prefix('/employee')->group(function () {
    Route::get('/detail/{id}', [EmployeeController::class, 'show']);
    Route::post('/create', [EmployeeController::class, 'store']);
    Route::post('/update/{id}', [EmployeeController::class, 'update']);
    Route::post('/delete/{id}', [EmployeeController::class, 'delete']);
});

Route::get('/divisions', [DivisionController::class, 'index']);
Route::prefix('/division')->group(function () {
    Route::get('/detail/{id}', [DivisionController::class, 'show']);
    Route::post('/create', [DivisionController::class, 'store']);
    Route::post('/update/{id}', [DivisionController::class, 'update']);
    Route::post('/delete/{id}', [DivisionController::class, 'delete']);
});

Route::get('/positions', [PositionController::class, 'index']);
Route::prefix('/position')->group(function () {
    Route::get('/detail/{id}', [PositionController::class, 'show']);
    Route::post('/create', [PositionController::class, 'store']);
    Route::post('/update/{id}', [PositionController::class, 'update']);
    Route::post('/delete/{id}', [PositionController::class, 'delete']);
});