<?php

use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ToolBookingController;
use App\Http\Controllers\ToolController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('rooms', RoomController::class);
Route::apiResource('tools', ToolController::class);

Route::post('rooms/bulk-destroy', [RoomController::class, 'bulkDestroy']);
Route::post('tools/bulk-destroy', [ToolController::class, 'bulkDestroy']);
Route::post('rooms/booking-rooms', [RoomBookingController::class, 'store']);
Route::delete('rooms/booking-rooms/{id}', [RoomBookingController::class, 'destroy']);
Route::post('tools/booking-tools', [ToolBookingController::class, 'store']);
Route::delete('tools/booking-tools/{id}', [ToolBookingController::class, 'destroy']);
