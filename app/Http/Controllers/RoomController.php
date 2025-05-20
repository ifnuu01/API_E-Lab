<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = Room::all()->map(function ($room) {
            $room->image = $room->image ? asset('storage/' . $room->image) : null;
            return $room;
        });
        return response()->json([
            'status' => true,
            'message' => 'List of rooms',
            'data' => $rooms
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code_room' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'code_room')
                    ->whereNull('deleted_at')
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['code_room', 'name', 'description', 'capacity', 'status']);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/rooms', 'public');
            $data['image'] = $imagePath;
        }

        $room = Room::create($data);

        $room->image_url = $room->image ? asset('storage/' . $room->image) : null;

        return response()->json([
            'status' => true,
            'message' => 'Room created successfully',
            'data' => $room
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'status' => false,
                'message' => 'Room not found',
                'data' => null
            ], 404);
        }

        $room->image = $room->image ? asset('storage/' . $room->image) : null;

        return response()->json([
            'status' => true,
            'message' => 'Room details',
            'data' => $room
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'status' => false,
                'message' => 'Room not found',
                'data' => null
            ], 404);
        }

        $request->validate([
            'code_room' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'code_room')
                    ->whereNull('deleted_at')
                    ->ignore($room->id)
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer',
            'image' => 'nullable|image|max:2048',
            'status' => 'required|in:available,unavailable'
        ]);

        $data = $request->only(['name', 'description', 'capacity', 'status']);
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/rooms', 'public');
            $data['image'] = $imagePath;
        }

        if ($room->image && Storage::disk('public')->exists($room->image)) {
            Storage::disk('public')->delete($room->image);
        }

        $room->update($data);
        $room->image_url = $room->image ? asset('storage/' . $room->image) : null;
        return response()->json([
            'status' => true,
            'message' => 'Room updated successfully',
            'data' => $room
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'status' => false,
                'message' => 'Room not found',
                'data' => null
            ], 404);
        }

        if ($room->image && Storage::disk('public')->exists($room->image)) {
            Storage::disk('public')->delete($room->image);
        }

        // $room->code_room = null;
        // $room->save();
        $room->delete();

        return response()->json([
            'status' => true,
            'message' => 'Room deleted successfully',
            'data' => null
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:rooms,code_room'
        ]);

        // jika salah satu data id di array ids tidak ada tampilkan pesan error
        $rooms = Room::whereIn('code_room', $request->ids)->get();
        if ($rooms->count() !== count($request->ids)) {
            return response()->json([
                'status' => false,
                'message' => 'Some rooms not found',
                'data' => null
            ], 404);
        }
        $count = 0;
        foreach ($request->ids as $id) {
            $room = Room::find($id);
            if ($room->image && Storage::disk('public')->exists($room->image)) {
                Storage::disk('public')->delete($room->image);
            }
            // $room->code_room = null;
            // $room->save();
            $room->delete();
            $count++;
        }

        return response()->json([
            'status' => true,
            'message' => "$count rooms deleted successfully",
            'data' => null
        ]);
    }
}
