<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomRequest;
use App\Models\Room;
use App\Models\RoomRequestDetail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RoomBookingController extends Controller
{
    public function getAllRoomBooking()
    {
        $roomRequests = RoomRequest::with('roomRequestDetails.room')->get();

        return response()->json([
            'status' => true,
            'message' => 'List of room booking requests',
            'data' => $roomRequests,
        ], 200);
    }

    public function store(Request $request)
    {

        $request->validate([
            'nim' => 'required|numeric|digits_between:10,12',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|numeric|digits_between:10,15',
            'address' => 'required|string|max:255',
            'borrow_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:255',
            'room_id' => 'required|array|min:1',
            'room_id.*' => 'required|exists:rooms,id',
        ]);


        foreach ($request->room_id as $roomId) {
            $room = Room::find($roomId);
            if (!$room) {
                return response()->json([
                    'status' => false,
                    'message' => 'Room not found',
                    'data' => null,
                ], 404);
            }

            $isConflict = RoomRequest::where('borrow_date', $request->borrow_date)
                ->where('status', 'approved')
                ->where('start_time', '<', $request->end_time)
                ->where('end_time', '>', $request->start_time)
                ->whereHas('roomRequestDetails', function ($query) use ($roomId) {
                    $query->where('room_id', $roomId);
                })
                ->exists();

            if ($isConflict) {
                return response()->json([
                    'status' => false,
                    'message' => "Room {$room->name} is already booked during this time.",
                    'data' => null,
                ], 409);
            }
        }


        do {
            $ticketCode = 'TICKET-ROOM-' . strtoupper(Str::random(10));
        } while (RoomRequest::where('ticket_code', $ticketCode)->exists());

        $roomRequest = RoomRequest::create([
            'nim' => $request->nim,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'borrow_date' => $request->borrow_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'purpose' => $request->purpose,
            'ticket_code' => $ticketCode
        ]);


        foreach ($request->room_id as $roomId) {
            RoomRequestDetail::create([
                'room_request_id' => $roomRequest->id,
                'room_id' => $roomId,
            ]);
        }

        Mail::to($roomRequest->email)->send(new \App\Mail\RoomBookingTicketMail($roomRequest));

        $roomRequest->load('roomRequestDetails.room');

        return response()->json([
            'status' => true,
            'message' => 'Room booking request created successfully',
            'data' => $roomRequest,
        ], 201);
    }

    public function destroy($id)
    {
        $roomRequest = RoomRequest::find($id);

        if (!$roomRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Room booking request not found',
                'data' => null,
            ], 404);
        }

        $roomRequest->delete();

        return response()->json([
            'status' => true,
            'message' => 'Room booking request deleted successfully',
            'data' => null,
        ]);
    }

    public function show($id)
    {
        $roomRequest = RoomRequest::find($id);

        if (!$roomRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Room booking request not found',
                'data' => null,
            ], 404);
        }

        $roomRequest->load('roomRequestDetails.room');

        return response()->json([
            'status' => true,
            'message' => 'Room booking request retrieved successfully',
            'data' => $roomRequest,
        ]);
    }

    public function update(Request $request, $id)
    {
        $roomRequest = RoomRequest::find($id);

        if (!$roomRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Room booking request not found',
                'data' => null,
            ], 404);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        // cek apakah pada tanggal dan jam yang sama sudah ada bookingan lain
        if ($request->status == 'approved') {
            $isConflict = RoomRequest::where('borrow_date', $roomRequest->borrow_date)
                ->where('status', 'approved')
                ->where('start_time', '<', $roomRequest->end_time)
                ->where('end_time', '>', $roomRequest->start_time)
                ->where('id', '!=', $roomRequest->id)
                ->exists();

            if ($isConflict) {
                return response()->json([
                    'status' => false,
                    'message' => 'Room booking conflict detected',
                    'data' => null,
                ], 409);
            }
        }

        $roomRequest->update([
            'status' => $request->status,
        ]);


        return response()->json([
            'status' => true,
            'message' => 'Room booking request updated successfully',
            'data' => $roomRequest,
        ]);
    }

    public function showByTicketCode($ticketCode)
    {
        $roomRequest = RoomRequest::where('ticket_code', $ticketCode)->first();

        if (!$roomRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Room booking request not found',
                'data' => null,
            ], 404);
        }

        $roomRequest->load('roomRequestDetails.room');

        return response()->json([
            'status' => true,
            'message' => 'Room booking request retrieved successfully',
            'data' => $roomRequest,
        ]);
    }

    public function cancelBooking($ticketCode)
    {
        $roomRequest = RoomRequest::where('ticket_code', $ticketCode)->first();

        if (!$roomRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Room booking request not found',
                'data' => null,
            ], 404);
        }

        $roomRequest->update(['status' => 'canceled']);

        return response()->json([
            'status' => true,
            'message' => 'Room booking request canceled successfully',
            'data' => $roomRequest,
        ]);
    }
}
