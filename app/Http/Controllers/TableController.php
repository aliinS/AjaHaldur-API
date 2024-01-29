<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use PhpParser\Node\Expr\Cast\String_;

class TableController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('amount', 4);
        $page = $request->get('page', 1);

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $tables = Table::where('owner_id', auth()->user()->id)
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json($tables);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        if ($request->type == 'personal' || $request->type == 'group') {
            // do nothing
        } else {
            return response()->json(['message' => "Invalid type. Type must be 'personal' or 'group'."], 400);
        }

        $table = Table::create([
            'title' => $request->title,
            'type' => $request->type, 
            'owner_id' => auth()->user()->id,
        ]);

        return response()->json(['table' => $table, 'message' => 'Creation successful'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        $table = Table::with('content')->find($id);
        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }
        return response()->json(['table' => $table], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id)
    {
        $table = Table::find($id);
        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }
        $table->update($request->all());
        return response()->json(['table' => $table, 'message' => 'Update successful'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $table = Table::find($id);
        if (!$table) {
            return response()->json(['message' => 'Table not found'], 404);
        }
        $table->delete();
        return response()->json(['message' => 'Deletion successful'], 200);
    }
}
