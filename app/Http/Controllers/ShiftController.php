<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Shift::sortBy('id', 'DESC')->get());
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
        $request->validate([
            'work_place' => 'required|text',
            'start_time' => 'required|timestamp',
            'end_time' => 'required|timestamp',
        ]);



        $shift = Shift::create($request->all());
        return response()->json($shift);
    }

    public function storeJob(Request $request, Shift $shift)
    {
        $request->validate([
            'shift_id' => 'required',
            'name' => 'required',
        ]);

        $shift->jobs()->create($request->all());
        $shift->save();
        return response()->json($shift);
    }

    public function storeStaff(Request $request, Shift $shift)
    {
        $request->validate([
            'shift_id' => 'required',
            'user_id' => 'required',
            'shift_job_id' => 'required',
        ]);

        $shift->staff()->create($request->all());
        $shift->save();
        return response()->json($shift);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        $shift->load('jobs');
        $shift->load('staff');
        return response()->json($shift);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'work_place' => 'required|text',
            'start_time' => 'required|timestamp',
            'end_time' => 'required|timestamp',
            'staff' => 'required|text',
        ]);

        $shift->update($request->all());
        return response()->json($shift);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        $shift->delete();
        return response()->json(['message' => 'Shift deleted']);
    }
}
