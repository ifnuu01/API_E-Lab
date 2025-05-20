<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ToolRequest;
use App\Models\ToolRequestDetail;
use App\Models\Tool;
use Illuminate\Support\Str;

class ToolBookingController extends Controller
{
    public function store(Request $request)
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

    public function destroy($id)
    {
        $toolRequest = ToolRequest::find($id);
        if (!$toolRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Tool booking request not found',
                'data' => null,
            ], 404);
        }

        $toolRequest->delete();

        return response()->json([
            'status' => true,
            'message' => 'Tool booking request deleted successfully',
            'data' => null,
        ], 200);
    }
}
