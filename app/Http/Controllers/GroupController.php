<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Table;
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

        Paginator::currentPageResolver(function () use ($page) {
            return $page;
        });

        $groups = User::with('groups')->find(auth()->user()->id)->groups()->orderBy('updated_at', 'desc')->paginate($perPage);
        foreach ($groups as $group) {
            if ($group->owner_id == auth()->user()->id) {
                $group['isOwner'] = true;
            } else {
                $group['isOwner'] = false;
            }
        }


        return response()->json($groups);
    }

    public function invite(Request $request)
    {
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



        $table = Table::create([
            'title' => $user->name,
            'type' => 'group',
            'owner_id' => $group->id,
            'group_member_id' => $user->id,
        ]);




        return response()->json(['message' => 'User added to group'], 200);
    }

    public function deleteMember(Request $request, String $id)
    {
        $request->validate([
            'user_id' => 'required|integer',
        ]);

        Log::info($request->user_id);
        Log::info($id);

        $groupUser = GroupUser::where('group_id', $id)->where('user_id', $request->user_id)->first();
        Log::info($groupUser);
        if (!$groupUser) {
            return response()->json(['message' => 'User not in group'], 404);
        }

        $groupOwner = Group::find($id)->owner_id;
        if ($groupOwner == $request->user_id) {
            return response()->json(['message' => 'Owner cannot be removed from group'], 400);
        }

        // get deleted users group table and archive it
        $table = Table::where('owner_id', $id)->where('group_member_id', $request->user_id)->where('archived', 0)->first();
        if ($table) {
            $table->update(['archived' => true]);
        }

        $groupUser->delete();
        return response()->json(['message' => 'User removed from group'], 200);
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

        $table = Table::create([
            'title' => auth()->user()->name,
            'type' => 'group',
            'owner_id' => $group->id,
            'group_member_id' => auth()->user()->id,
        ]);


        return response()->json(['message' => 'Group created successfully'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        // check if user is in the group
        $groupUser = GroupUser::where('group_id', $id)->where('user_id', auth()->user()['id'])->first();
        if (!$groupUser) {
            return response()->json(['message' => 'User not in group'], 404);
        }


        if (!Group::find($id)) {
            return response()->json(['message' => 'Group not found'], 404);
        }
        $group = Group::with('users')->with('tables')->find($id);

        Log::info(auth()->user()['id']);

        $group['membersList'] = $group->users()->get();

        if (auth()->user()['id']  == $group->owner_id) {
            $group['isOwner'] = true;
            return response()->json(['group' => $group], 200);
        } else {
            $group['isOwner'] = false;
            $data = [];
            // add group data to the $data
            // $data = $group;

            $data['name'] = $group['name'];
            $data['description'] = $group['description'];
            $data['isOwner'] = $group['isOwner'];
            $data['membersList'] = $group['membersList'];
            // add users's table to data
            $data['tables'] = $group->tables()->where('group_member_id', auth()->user()['id'])->where('archived', 0)->get();
            // Log::info('tables');
            // add users's data to the $data
            $data['users'] = $group->users()->where('user_id', auth()->user()['id'])->get();
            return response()->json(['group' => $data], 200);
        }


        // respond with the group and its users
        // return response()->json(['group' => $group], 200);
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
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }
        $mebers = GroupUser::where('group_id', $id)->get();
        foreach ($mebers as $member) {
            $member->delete();
        }
        $group->delete();
        return response()->json(['message' => 'Group deleted successfully'], 200);
    }
}
