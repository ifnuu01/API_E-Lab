<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ToolRequest;
use App\Models\ToolRequestDetail;
use App\Models\Tool;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ToolBookingController extends Controller
{

    public function getAllToolBooking()
    {
        $toolRequests = ToolRequest::with('toolRequestDetails.tool')->get();

        return response()->json([
            'status' => true,
            'message' => 'List of tool booking requests',
            'data' => $toolRequests,
        ], 200);
    }

    public function createToolRequest(Request $request)
    {
        $request->validate([
            'nim' => 'required|numeric|digits_between:10,12',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|numeric|digits_between:10,15',
            'address' => 'required|string|max:255',
            'borrow_date' => 'required|date',
            'purpose' => 'required|string|max:5000',
            'tool_id' => 'required|array|min:1',
            'tool_id.*' => 'required|exists:tools,id',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|integer|min:1',
        ]);


        foreach ($request->tool_id as $index => $toolId) {
            $tool = Tool::find($toolId);
            if (!$tool) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tool not found',
                    'data' => null,
                ], 404);
            }

            $qtyRequested = $request->quantity[$index];

            $bookedQty = ToolRequestDetail::where('tool_id', $toolId)
                ->whereHas('toolRequest', function ($q) use ($request) {
                    $q->where('borrow_date', $request->borrow_date)
                        ->where('status', 'approved')
                        ->where('expiration_date', '>=', now());
                })
                ->sum('quantity');

            if ($bookedQty + $qtyRequested > $tool->available_quantity) {
                return response()->json([
                    'status' => false,
                    'message' => "Requested quantity for tool {$tool->name} exceeds available quantity.",
                    'data' => null,
                ], 409);
            }
        }

        do {
            $ticketCode = 'TICKET-TOOL-' . strtoupper(Str::random(10));
        } while (ToolRequest::where('ticket_code', $ticketCode)->exists());


        $toolRequest = ToolRequest::create([
            'nim' => $request->nim,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'borrow_date' => $request->borrow_date,
            'expiration_date' => now()->addDays(3),
            'purpose' => $request->purpose,
            'ticket_code' => $ticketCode,
        ]);


        foreach ($request->tool_id as $index => $toolId) {
            ToolRequestDetail::create([
                'tool_request_id' => $toolRequest->id,
                'tool_id' => $toolId,
                'quantity' => $request->quantity[$index],
            ]);
        }

        $toolRequest->load('toolRequestDetails.tool');

        return response()->json([
            'status' => true,
            'message' => 'Tool booking request created successfully',
            'data' => $toolRequest,
        ], 201);
    }

    public function destroyToolRequest($id)
    {
        $toolRequest = ToolRequest::find($id);
        if (!$toolRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Tool booking request not found',
                'data' => null,
            ], 404);
        }

        if ($toolRequest->image && Storage::disk('public')->exists($toolRequest->image)) {
            Storage::disk('public')->delete($toolRequest->image);
        }

        $toolRequest->delete();

        return response()->json([
            'status' => true,
            'message' => 'Tool booking request deleted successfully',
            'data' => null,
        ], 200);
    }


    public function showToolRequest($id)
    {
        $toolRequest = ToolRequest::find($id);

        if (!$toolRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Tool booking request not found',
                'data' => null,
            ], 404);
        }

        $toolRequest->load('toolRequestDetails.tool');

        return response()->json([
            'status' => true,
            'message' => 'Tool booking request retrieved successfully',
            'data' => $toolRequest,
        ], 200);
    }

    public function updateRequest(Request $request, $id)
    {
        $toolRequest = ToolRequest::with('toolRequestDetails')->find($id);

        if (!$toolRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Tool booking request not found',
                'data' => null,
            ], 404);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);
        if ($request->status === 'approved') {
            // Hitung total quantity per tool_id (jika ada alat sama dipinjam dua kali)
            $toolQuantities = [];
            foreach ($toolRequest->toolRequestDetails as $detail) {
                $toolQuantities[$detail->tool_id] = ($toolQuantities[$detail->tool_id] ?? 0) + $detail->quantity;
            }

            // Cek ketersediaan alat
            foreach ($toolQuantities as $toolId => $qtyRequested) {
                $tool = Tool::find($toolId);
                if (!$tool) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Tool not found',
                        'data' => null,
                    ], 404);
                }

                if ($qtyRequested > $tool->available_quantity) {
                    return response()->json([
                        'status' => false,
                        'message' => "Requested quantity for tool {$tool->name} exceeds available quantity.",
                        'data' => null,
                    ], 409);
                }
            }

            // Jika semua cukup, kurangi stok
            foreach ($toolQuantities as $toolId => $qtyRequested) {
                $tool = Tool::find($toolId);
                $tool->available_quantity -= $qtyRequested;
                $tool->save();
            }

            $toolRequest->status = 'approved';
            $toolRequest->save();

            $toolRequest->load('toolRequestDetails.tool');

            return response()->json([
                'status' => true,
                'message' => 'Tool booking request approved successfully',
                'data' => $toolRequest,
            ], 200);
        } else {
            // Jika rejected
            $toolRequest->status = 'rejected';
            $toolRequest->save();

            return response()->json([
                'status' => true,
                'message' => 'Tool booking request rejected',
                'data' => $toolRequest,
            ], 200);
        }
    }

    public function showByTicketCode($ticketCode)
    {
        $toolRequest = ToolRequest::where('ticket_code', $ticketCode)->first();

        if (!$toolRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Tool booking request not found',
                'data' => null,
            ], 404);
        }

        $toolRequest->load('toolRequestDetails.tool');

        return response()->json([
            'status' => true,
            'message' => 'Tool booking request retrieved successfully',
            'data' => $toolRequest,
        ], 200);
    }

    public function cancelBooking($ticketCode)
    {
        $toolRequest = ToolRequest::where('ticket_code', $ticketCode)->first();

        if (!$toolRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Tool booking request not found',
                'data' => null,
            ], 404);
        }

        $toolRequest->update(['status' => 'canceled']);

        return response()->json([
            'status' => true,
            'message' => 'Tool booking request canceled successfully',
            'data' => $toolRequest,
        ], 200);
    }

    public function returnTools(Request $request, $detailId)
    {
        $detail = ToolRequestDetail::find($detailId);
        if (!$detail) {
            return response()->json([
                'status' => false,
                'message' => 'Tool request detail not found',
                'data' => null,
            ], 404);
        }

        if ($detail->toolRequest->status !== 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'Tool request must be approved before returning',
                'data' => null,
            ], 400);
        }

        if ($detail->toolRequest->status === 'returned') {
            return response()->json([
                'status' => false,
                'message' => 'Tool has already been returned',
                'data' => null,
            ], 400);
        }

        $request->validate([
            'return_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('return_image')) {
            $imagePath = $request->file('return_image')->store('images/proof', 'public');
            $detail->return_image = $imagePath;
        }

        $detail->return_date = now();
        $detail->save();

        return response()->json([
            'status' => true,
            'message' => 'Tool returned successfully',
            'data' => $detail,
        ], 200);
    }

    public function updateReturnStatus(Request $request, $detailId)
    {
        $detail = ToolRequestDetail::find($detailId);
        if (!$detail) {
            return response()->json([
                'status' => false,
                'message' => 'Tool request detail not found',
                'data' => null,
            ], 404);
        }

        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        // jika status accepted, tambah stok
        if ($request->status === 'accepted') {
            $tool = Tool::find($detail->tool_id);
            if (!$tool) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tool not found',
                    'data' => null,
                ], 404);
            }

            $tool->available_quantity += $detail->quantity;
            $tool->save();
        }

        $detail->status = $request->status;
        $detail->save();

        return response()->json([
            'status' => true,
            'message' => 'Tool return status updated successfully',
            'data' => $detail,
        ], 200);
    }
}
