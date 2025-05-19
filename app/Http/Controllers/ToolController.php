<?php

namespace App\Http\Controllers;

use App\Models\tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tools = Tool::all()->map(function ($tool) {
            $tool->image = $tool->image ? asset('storage/' . $tool->image) : null;
            return $tool;
        });
        return response()->json([
            'status' => true,
            'message' => 'List of tools',
            'data' => $tools
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['code_tool', 'name', 'description', 'quantity']);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/tools', 'public');
            $data['image'] = $imagePath;
        }

        $tool = Tool::create([
            'name' => $data['name'],
            'description' => $data['description'],
            'quantity' => $data['quantity'],
            'available_quantity' => $data['quantity'],
            'image' => $data['image'] ?? null
        ]);

        $tool->image_url = $tool->image ? asset('storage/' . $tool->image) : null;

        return response()->json([
            'status' => true,
            'message' => 'Tool created successfully',
            'data' => $tool
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tool = Tool::find($id);

        if (!$tool) {
            return response()->json([
                'status' => false,
                'message' => 'Tool not found',
                'data' => null
            ], 404);
        }

        $tool->image_url = $tool->image ? asset('storage/' . $tool->image) : null;

        return response()->json([
            'status' => true,
            'message' => 'Tool details',
            'data' => $tool
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tool = Tool::find($id);

        if (!$tool) {
            return response()->json([
                'status' => false,
                'message' => 'Tool not found',
                'data' => null
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quantity' => 'required|integer',
            'available_quantity' => 'required|integer',
            'status' => 'required|in:available,unavailable',
            'image' => 'nullable|image|max:2048'
        ]);

        $data = $request->only(['name', 'description', 'quantity', 'available_quantity', 'status']);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images/tools', 'public');
            $data['image'] = $imagePath;
        }

        $tool->update($data);

        $tool->image_url = $tool->image ? asset('storage/' . $tool->image) : null;

        return response()->json([
            'status' => true,
            'message' => 'Tool updated successfully',
            'data' => $tool
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tool = Tool::find($id);

        if (!$tool) {
            return response()->json([
                'status' => false,
                'message' => 'Tool not found',
                'data' => null
            ], 404);
        }

        if ($tool->image && Storage::disk('public')->exists($tool->image)) {
            Storage::disk('public')->delete($tool->image);
        }

        $tool->delete();

        return response()->json([
            'status' => true,
            'message' => 'Tool deleted successfully',
            'data' => null
        ]);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:tools,id'
        ]);

        $tools = Tool::whereIn('id', $request->ids)->get();

        foreach ($tools as $tool) {
            if ($tool->image && Storage::disk('public')->exists($tool->image)) {
                Storage::disk('public')->delete($tool->image);
            }
            $tool->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Tools deleted successfully',
            'data' => null
        ]);
    }
}
