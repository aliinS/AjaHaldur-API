<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiftController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['register', 'login']]);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index($group_id)
    {
        return response()->json(Shift::where('group_id', $group_id)->orderBy('id', 'DESC')->get());
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

        $group = Group::findOrFail($request->group_id);
        if ($group->owner_id !== auth()->user()->id) {
            return response()->json(['message' => 'You are not the owner of this group'], 403);
        }

        $request->validate([
            'group_id' => 'required|integer|exists:groups,id',
            'work_place' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);



        $shift = Shift::create([
            'group_id' => $request->group_id,
            'work_place' => $request->work_place,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);
        return response()->json($shift);
    }

    public function storeJob(Request $request, Shift $shift)
    {
        $group = Group::findOrFail($shift->group_id);
        if ($group->owner_id !== auth()->user()->id) {
            return response()->json(['message' => 'You are not the owner of this group'], 403);
        }

        $request->merge(['shift_id' => $shift->id]);

        $request->validate([
            'shift_id' => 'required|integer|exists:shifts,id',
            'name' => 'required',
        ]);

        $shift->jobs()->create([
            'shift_id' => $request->shift_id,
            'name' => $request->name,
        ]);
        $shift->save();
        return response()->json($shift);
    }

    public function storeStaff(Request $request, Shift $shift)
    {

        $group = Group::findOrFail($shift->group_id);
        if ($group->owner_id !== auth()->user()->id) {
            return response()->json(['message' => 'You are not the owner of this group'], 403);
        }

        $request->merge(['shift_id' => $shift->id]);

        $request->validate([
            'shift_id' => 'required|integer|exists:shifts,id',
            'user_id' => 'required|integer|exists:users,id',
            'shift_job_id' => 'required|integer|exists:shift_jobs,id',
        ]);

        $shift->staff()->create([
            'shift_id' => $request->shift_id,
            'user_id' => $request->user_id,
            'shift_job_id' => $request->shift_job_id,
        ]);
        $shift->save();
        return response()->json($shift);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        Log::info('Showing shift: ' . $shift);
        // check if shift exiasts
        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }

        $shift->load('jobs', 'staff', 'staff.user', 'staff.shift_job');

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

        $group = Group::findOrFail($shift->group_id);
        if ($group->owner_id !== auth()->user()->id) {
            return response()->json(['message' => 'You are not the owner of this group'], 403);
        }

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

        $group = Group::findOrFail($shift->group_id);
        if ($group->owner_id !== auth()->user()->id) {
            return response()->json(['message' => 'You are not the owner of this group'], 403);
        }

        Log::info('Deleting shift: ' . $shift->name);

        $shift->jobs()->delete();
        $shift->staff()->delete();

        $shift->delete();
        return response()->json(['message' => 'Shift deleted']);
    }
}
