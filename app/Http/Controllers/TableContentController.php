<?php

namespace App\Http\Controllers;

use App\Models\TableContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'location' => 'max:255',
            'table_id' => 'required|integer',
        ]);

        $content = TableContent::create([
            'date' => $request->date,
            'time' => $request->time,
            'location' => $request->location,
            'table_id' => $request->table_id,
        ]);
        // Retrun a response with a JSON object
        return response()->json(['content' => $content, 'message' => 'Sissekanne edulakt loodud'], 201);
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
        $tableContent = TableContent::find($id);
        if (!$tableContent) {
            return response()->json(['message' => 'Sissekannet pole olemas'], 404);
        }
        
        // Validate the request
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|string',
            'location' => 'max:255',
        ]);

        $tableContent->update($request->all());
        return response()->json(['content' => $tableContent, 'message' => 'Sissekanne uuendatud edukalt'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $tableContent = TableContent::find($id);
        if (!$tableContent) {
            return response()->json(['message' => 'Sissekannet pole olemas'], 404);
        }

        
        Log::info($tableContent);
        $tableContent->delete();
        return response()->json(['message' => 'Sissekanne edukalt kustutatud'], 200);
    }
}
