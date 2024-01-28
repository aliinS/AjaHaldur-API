<?php

namespace App\Http\Controllers;

use App\Models\TableContent;
use Illuminate\Http\Request;

class TableContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|string',
            'location' => 'required|string|max:255',
            'table_id' => 'required|integer',
        ]);

        $content = TableContent::create([
            'date' => $request->date,
            'time' => $request->time,
            'location' => $request->location,
            'table_id' => $request->table_id,
        ]);
        // Retrun a response with a JSON object
        return response()->json(['content' => $content, 'message' => 'Creation successful'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TableContent $tableContent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TableContent $tableContent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        // Validate the request
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|string',
            'location' => 'required|string|max:255',
            'table_id' => 'required|integer',
        ]);

        $content = TableContent::find($id);
        if (!$content) {
            return response()->json(['message' => 'Content not found'], 404);
        }
        $content->update($request->all());
        return response()->json(['content' => $content, 'message' => 'Content updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $content = TableContent::find($id);
        if (!$content) {
            return response()->json(['message' => 'Content not found'], 404);
        }
        $content->delete();
        return response()->json(['message' => 'Content deleted successfully'], 200);
    }
}
