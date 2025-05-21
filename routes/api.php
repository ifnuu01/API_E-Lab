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

Route::post('rooms/bulk-destroy', [RoomController::class, 'bulkDestroy']);
Route::post('tools/bulk-destroy', [ToolController::class, 'bulkDestroy']);


Route::get('rooms/booking-rooms', [RoomBookingController::class, 'getAllRoomBooking']);
Route::get('rooms/booking-rooms/{id}', [RoomBookingController::class, 'show']);
Route::post('rooms/booking-rooms', [RoomBookingController::class, 'store']);
Route::put('rooms/booking-rooms/{id}/status', [RoomBookingController::class, 'update']);
Route::delete('rooms/booking-rooms/{id}', [RoomBookingController::class, 'destroy']);

Route::get('rooms/ticket/{tickedCode}', [RoomBookingController::class, 'showByTicketCode']);


// List semua booking tools
Route::get('tools/booking-tools', [ToolBookingController::class, 'getAllToolBooking']);

// Buat booking tools baru
Route::post('tools/booking-tools', [ToolBookingController::class, 'createToolRequest']);

// Detail booking tools by ID
Route::get('tools/booking-tools/{id}', [ToolBookingController::class, 'showToolRequest']);

// Update booking tools by ID
Route::put('tools/booking-tools/{id}/status', [ToolBookingController::class, 'updateRequest']);

// Update status pengembalian alat
Route::put('tools/booking-tools/{id}/return-status', [ToolBookingController::class, 'updateReturnStatus']);

// Hapus booking tools by ID
Route::delete('tools/booking-tools/{id}', [ToolBookingController::class, 'destroyToolRequest']);

// Get booking tools by ticket code
Route::get('tools/ticket/{ticketCode}', [ToolBookingController::class, 'showByTicketCode']);

// Cancel booking tools by ticket code
Route::put('tools/booking-tools/ticket/{ticketCode}/cancel', [ToolBookingController::class, 'cancelBooking']);

// Return alat (single detail)
Route::post('tools/booking-tools/return-image/{detailId}', [ToolBookingController::class, 'returnTools']);

// Return alat (multi detail)
Route::post('tools/booking-tools/return-image/multi', [ToolBookingController::class, 'returnMultiTools']);

// Hapus gambar return (single)
Route::delete('tools/booking-tools/return-image/{detailId}', [ToolBookingController::class, 'deleteReturnImage']);

// Hapus gambar return (multi)
Route::post('tools/booking-tools/return-image/bulk-delete', [ToolBookingController::class, 'bulkDeleteReturnImages']);


Route::apiResource('rooms', RoomController::class);
Route::apiResource('tools', ToolController::class);
