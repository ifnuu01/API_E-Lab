<?php

namespace App\Http\Controllers;

use App\Models\tool;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(tool::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $tool = tool::create($request->all());
            return response()->json([
                'message' => 'Tool created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'Failed to create tool',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $tool = tool::find($id);
        if (!$tool) {
            return response()->json(['message' => 'Tool not found'], 404);
        }
        return response()->json($tool);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $tool = tool::find($id);
        if (!$tool) {
            return response()->json(['message' => 'Tool not found'], 404);
        }
        return response()->json($tool);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tool = tool::find($id);
        if (!$tool) {
            return response()->json(['message' => 'Tool not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:available,unavailable',
        ]);

        try {
            $tool->update($request->all());
            return response()->json([
                'message' => 'Tool updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'Failed to update tool',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tool = tool::find($id);
        if (!$tool) {
            return response()->json(['message' => 'Tool not found'], 404);
        }

        try {
            $tool->delete();
            return response()->json([
                'message' => 'Tool deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'Failed to delete tool',
                    'error' => $e->getMessage(),
                ],
                500
            );
        }
    }
}
