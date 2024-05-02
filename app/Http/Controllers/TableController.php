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

    public function __invoke()
    {
        
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

        $tables = Table::where('owner_id', auth()->user()->id)->where('type', 'personal')
            ->orderBy('updated_at', 'desc')
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
            return response()->json(['message' => "Süsteemi viga: TAB-001"], 400);
        }

        $table = Table::create([
            'title' => $request->title,
            'type' => $request->type,
            'owner_id' => auth()->user()->id,
        ]);

        return response()->json(['table' => $table, 'message' => 'Tabel edukalt loodud'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function hours(String $id)
    {
        $table = Table::with('content')->find($id);
        $hours = 0;

        foreach ($table->content as $content) {
            $hours += $content->time;
        }

        return response()->json(['hours' => $hours], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        $hours = 0;
        // $table = Table::with('content')->find($id);
        // get table with content that is sorted by id
        $table = Table::with(['content' => function ($query) {
            $query->orderBy('id', 'desc');
        }])->find($id);
        
        if (!$table) {
            return response()->json(['message' => 'Tabel pole olemas'], 404);
        }
        if ($table->owner_id != auth()->user()->id) {
            return response()->json(['message' => 'Volitamata'], 401);
        }
        // $table->content = $table->content->sortByDesc('id');
        foreach ($table->content as $content) {
            $hours += $content->time;
        }
        $table->hours = $hours;
        Log::info($table->content);
        return response()->json(['table' => $table, "message" => 'Tabel edukalt laetud'], 200);
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

        $request->validate([
            'title' => 'string|max:255',
            'type' => 'string|max:255',
            
        ]);

        if ($request->type == 'personal' || $request->type == 'group') {
            // do nothing
        } else {
            return response()->json(['message' => "Süsteemi viga: TAB-001"], 400);
        }

        
        $table::update($request->all()); 

        
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
