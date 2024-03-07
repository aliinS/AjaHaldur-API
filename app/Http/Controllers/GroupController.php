<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\GroupUse;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('amount', 4);
        $page = $request->get('page', 1);

        $groups = User::with('groups')->find(auth()->user()->id)->groups()->paginate($perPage);

        return response()->json($groups);
    }

    public function invite(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'group_id' => 'required|integer',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $group = Group::find($request->group_id);
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        $groupUser = GroupUser::where('group_id', $group->id)->where('user_id', $user->id)->first();
        if ($groupUser) {
            return response()->json(['message' => 'User already in group'], 400);
        }

        GroupUser::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => 'User added to group'], 200);
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
            'name' => 'required|string|max:255',
        ]);

        $group = Group::create([
            'name' => $request->name,
            'owner_id' => auth()->user()->id,
        ]);

        GroupUser::create([
            'group_id' => $group->id,
            'user_id' => auth()->user()->id,
        ]);


        return response()->json(['message' => 'Group created successfully'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        // respond with the group and its users
        $group = Group::with('users')->with('tables')->find($id);
        return response()->json(['group' => $group], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $group->update($request->all());
        return response()->json(['message' => 'Group updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id)
    {
        $group = Group::find($id);
        $group->delete();
        return response()->json(['message' => 'Group deleted successfully'], 200);
    }
}
