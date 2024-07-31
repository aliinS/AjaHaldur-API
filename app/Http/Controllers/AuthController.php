<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Faker\Generator as Faker;
use Illuminate\Auth\Events\Registered;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['register', 'login', 'appRegister']]);
    }

    // update user data
    public function update(Request $request)
    {
        $user = User::find(Auth::user()->id);

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|email:dns,rfc|unique:users,email,' . $user->id . '|max:255',
            'password' => 'string|min:6',
        ]);

        if ($request->password) {
            $user->update(array_merge($request->all(), ['password' => bcrypt($request->password)]));
        } else {
            $user->update($request->all());
        }

        // return response()->json(['user' => $user, 'message' => 'User updated successfully'], 200);
        return response()->json(['message' => 'User updated successfully'], 200);
    }

    // function that deletes a user an all tables and tablecointents that belong to him
    public function delete(Request $request)
    {
        $user = User::with('groups')->find(Auth::user()->id);

        $personalTables = Table::where('type', 'personal')->where('owner_id', $user->id)->get();
        // $groupTables = Table::where('type', 'group')->where('owner_id', $user->id)->get();
        $groups = $user->groups;



        // loop each group to create a list of pivots, so they can be deleted too
        foreach ($groups as $group) {
            $groupTables = $group->pivot;
            // GroupUser::where('group_id', $groupTable->group_id)->where('user_id', $groupTable->user_id)->get();
            foreach ($groupTables as $groupTable) {
                // Log::info($groupTable);
            }
            // GroupUser::where('group_id', $groupTables->group_id)->where('user_id', $groupTables->user_id)->delete();
            // Log::info($groupTables);
            // Log::info(GroupUser::where('group_id', $groupTables->group_id)->where('user_id', $groupTables->user_id)->get());

            // Group::where('id', $groupTables->group_id);
            // Log::info(Group::where('id', $groupTables->group_id));
        }

        // Log::info($groups);

        // $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    // Register a new user
    public function register(Request $request)
    {
        $credentials = $request->only('name', 'email', 'password');

        $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|email:dns,rfc|unique:users|max:255',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // return response()->json(['user' => $user, 'message' => 'Registration successful'], 201);
        $token = Auth::guard('api')->login($user);

        $token = $this->respondWithToken($token)->original;

        // $cookie = Cookie::make('token', $token, 1440, null, null, true, true);

        return response()->json(compact('user', 'token'), 201);
    }

    public function appRegister(Request $request)
    {
        $credentials = $request->only('name', 'email', 'password');

        $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|email:dns,rfc|unique:users|max:255',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // return response()->json(['user' => $user, 'message' => 'Registration successful'], 201);
        $token = $user->createToken('auth_token')->plainTextToken;

        // $cookie = Cookie::make('token', $token, 1440, null, null, true, true);

        return response()->json(compact('token'), 201);
    }

    // Login an existing user
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    // Logout
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    public function appLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        $cookie = Cookie::make('token', $token, 1440, null, null, true, true);

        // $ttl = auth()->factory()->getTTL(); // Get the TTL value
        $ttl = 120; // Get the TTL value
        $expiration = now()->addMinutes($ttl);

        // Generate a refresh token
        // $refreshToken = JWTAuth::refresh($token, true);

        // Log::info($ttl);

        return response()->json([
            'access_token' => $token,
            // 'refresh_token' => $refreshToken, // Include the refresh token in the response
            'token_type' => 'bearer',
            'expires_in' => $expiration,
        ])->withCookie($cookie);
    }
}
