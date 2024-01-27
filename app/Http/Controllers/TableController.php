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
    
        return response()->json(Table::where('owner_id', auth()->user()->id)->paginate($perPage));
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
        $table = Table::find($id);
        return response()->json(['table' => $table], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Table $table)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Table $table)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Table $table)
    {
        //
    }
}
