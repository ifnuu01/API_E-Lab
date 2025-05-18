<?php

namespace App\Http\Controllers;

use App\Models\room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(room::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_room' => 'required|string|max:255|unique:rooms',
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
        ]);
        try {
            $room = room::create($validated);
            return response()->json([
                'message' => 'Room created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create room',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($code_room)
    {
        $room = room::find($code_room);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }
        return response()->json($room);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $code_room)
    {
        $room = room::find($code_room);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        $validated = $request->validate([
            'code_room' => 'required|string|max:255|unique:rooms,code_room,' . $room->code_room . ',code_room',
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
        ]);

        try {
            $room->update($validated);
            return response()->json([
                'message' => 'Room updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update room',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($code_room)
    {
        $room = room::find($code_room);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        try {
            $room->delete();
            return response()->json(['message' => 'Room deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete room',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
