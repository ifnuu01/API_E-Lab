<?php

use App\Http\Controllers\AdminAuthController;
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
// test token
// 1|qtX45SfUruxHnuOGO29k23aEmp67U5ua7Yhdxgyscbc6c902

// AUTH (TIDAK PERLU LOGIN)
Route::post('admin/login', [AdminAuthController::class, 'login']);
Route::post('admin/forgot-password', [AdminAuthController::class, 'forgotPassword']);
Route::post('admin/reset-password', [AdminAuthController::class, 'resetPassword']);

// ENDPOINT YANG PERLU LOGIN
Route::middleware('auth:sanctum')->group(function () {
    // Authenticated admin actions
    Route::post('admin/logout', [AdminAuthController::class, 'logout']);
    Route::put('admin/profile', [AdminAuthController::class, 'updateProfile']);
    Route::put('admin/password', [AdminAuthController::class, 'updatePassword']);

    // Endpoint yang hanya boleh diakses setelah login
    // CRUD rooms dan tools
    Route::apiResource('rooms', RoomController::class);
    Route::post('rooms/bulk-destroy', [RoomController::class, 'bulkDestroy']);
    Route::apiResource('tools', ToolController::class);
    Route::post('tools/bulk-destroy', [ToolController::class, 'bulkDestroy']);

    // manajemen booking rooms
    Route::get('rooms/booking-rooms', [RoomBookingController::class, 'index']);
    Route::get('rooms/booking-rooms/{id}', [RoomBookingController::class, 'show'])->where('id', '[0-9]+');
    Route::put('rooms/booking-rooms/{id}/status', [RoomBookingController::class, 'update']);
    Route::delete('rooms/booking-rooms/{id}', [RoomBookingController::class, 'destroy']);
    // manajemen booking tools
    Route::get('tools/booking-tools', [ToolBookingController::class, 'index']);
    Route::get('tools/booking-tools/{id}', [ToolBookingController::class, 'showToolRequest'])->where('id', '[0-9]+');
    Route::put('tools/booking-tools/{id}/status', [ToolBookingController::class, 'updateRequest']);
    Route::put('tools/booking-tools/{id}/return-status', [ToolBookingController::class, 'updateReturnStatus']);
    Route::delete('tools/booking-tools/{id}', [ToolBookingController::class, 'destroyToolRequest']);
});

// ENDPOINT YANG TIDAK PERLU LOGIN (PUBLIC)
// peminjaman ruangan
Route::post('rooms/booking-rooms', [RoomBookingController::class, 'store']);
Route::get('rooms/booking-rooms/ticket/{ticketCode}', [RoomBookingController::class, 'showByTicketCode']);
Route::put('rooms/booking-rooms/{ticketCode}/cancel', [RoomBookingController::class, 'cancelBooking']);
// peminjaman alat
Route::post('tools/booking-tools', [ToolBookingController::class, 'createToolRequest']);
Route::get('tools/booking-tools/ticket/{ticketCode}', [ToolBookingController::class, 'showByTicketCode']);
Route::put('tools/booking-tools/ticket/{ticketCode}/cancel', [ToolBookingController::class, 'cancelBooking']);
Route::post('tools/booking-tools/return-image/{detailId}', [ToolBookingController::class, 'returnTools']);
Route::post('tools/booking-tools/return-image/multi', [ToolBookingController::class, 'returnMultiTools']);
Route::delete('tools/booking-tools/return-image/{detailId}', [ToolBookingController::class, 'deleteReturnImage']);
Route::post('tools/booking-tools/return-image/bulk-delete', [ToolBookingController::class, 'bulkDeleteReturnImages']);
