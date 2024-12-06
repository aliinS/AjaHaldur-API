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
use Illuminate\Support\Facades\DB;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['login', 'register', 'appRegister', 'getAvatar']]);

    }

    public function getAvatar($uuid, $size = 'original')
    {
        $avatarField = 'avatar_' . $size;
        
        $user = User::where('uuid', $uuid)->first();
        
        if (!$user || !$user->$avatarField) {
            return $this->getDefaultAvatar();
        }
        
        $path = storage_path('app/public/' . $user->$avatarField);
        
        if (!file_exists($path)) {
            return $this->getDefaultAvatar();
        }
        
        return response()->file($path);
    }

    private function getDefaultAvatar()
    {
        $avatarName = 'default-avatar.jpg';
        $defaultAvatar = url($avatarName);
        $defaultPath = public_path($avatarName);

        // dd($defaultPath);
        
        if (!file_exists($defaultPath)) {
            return response()->json(['error' => 'Default avatar not found'], 404);
        }
        
        return response()->file($defaultPath);
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
        $myGroups = $user->groups;
        
        foreach ($personalTables as $personalTable) {
            $personalTable->delete();
        }

        foreach ($myGroups as $group) {
            if ($group->owner_id == $user->id) {
                $groupInstance = Group::find($group->id);

                if (count($groupInstance->membersList) == 1) {
                    if ($groupInstance->membersList[0]->id == $user->id) {
                        foreach ($groupInstance->tables as $table) {
                            $table->delete();
                        }
                        $groupInstance->delete();
                    }
                }
            }
        }

        $user->name = '_deleted_' . Str::uuid();
        $user->email = '_deleted_' . Str::uuid();
        $user->password = bcrypt('_deleted_' . Str::uuid());
        $user->avatar_original = null;
        $user->avatar_medium = null;
        $user->avatar_small = null;
        $user->avatar_thumbnail = null;
        $user->deleted = true;
        $user->save();

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

        $token = $user->createToken('auth_token')->plainTextToken;

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
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }
    
    public function appLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->noContent();
    }

    public function me()
    {
        $user = auth()->user();

        if ($user->avatar_original) {
            $user->avatar_original = asset('storage/' . $user->avatar_original);
            $user->avatar_medium = asset('storage/' . $user->avatar_medium);
            $user->avatar_small = asset('storage/' . $user->avatar_small); 
            $user->avatar_thumbnail = asset('storage/' . $user->avatar_thumbnail);
        }

        return response()->json($user);
    }

    public function updateAvatar(Request $request)
    {
        $user = User::find(Auth::user()->id);

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $avatar = $request->file('avatar');
        $timestamp = time();
        $extension = $avatar->getClientOriginalExtension();
        
        $avatarPaths = [];
        
        // Store original file first
        $originalFileName = "{$timestamp}_original.{$extension}";
        $storagePath = "avatars/{$originalFileName}";
        Storage::disk('public')->put($storagePath, file_get_contents($avatar));
        $avatarPaths["avatar_original"] = $storagePath;

        // Generate different sizes
        $sizes = [
            'medium' => ['width' => 600, 'height' => 600],
            'small' => ['width' => 300, 'height' => 300],
            'thumbnail' => ['width' => 150, 'height' => 150],
        ];

        foreach ($sizes as $size => $dimensions) {
            $fileName = "{$timestamp}_{$size}.{$extension}";
            $tempPath = storage_path("app/temp/{$fileName}");
            
            // Ensure temp directory exists
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0777, true);
            }

            // Create optimized image
            Image::load($avatar)
                ->optimize()
                ->width($dimensions['width'])
                ->height($dimensions['height'])
                ->save($tempPath);

            // Store in public storage and get URL
            $storagePath = "avatars/{$fileName}";
            Storage::disk('public')->put($storagePath, file_get_contents($tempPath));
            unlink($tempPath);
                
            $avatarPaths["avatar_{$size}"] = $storagePath;
        }

        // Delete old avatars
        $sizeKeys = array_merge(['original'], array_keys($sizes));
        foreach ($sizeKeys as $size) {
            $oldAvatar = $user["avatar_{$size}"];
            if ($oldAvatar && Storage::disk('public')->exists($oldAvatar)) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }

        $user->update($avatarPaths);

        // Generate full URLs for response
        $avatarUrls = array_map(function($path) {
            return asset('storage/' . $path);
        }, $avatarPaths);

        return response()->json([
            'message' => 'Avatar updated successfully',
            'avatar' => $avatarUrls
        ], 200);
    }

    public function deleteAvatar(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $user->update(['avatar_original' => null, 'avatar_medium' => null, 'avatar_small' => null, 'avatar_thumbnail' => null]);
        return response()->json(['message' => 'Avatar deleted successfully'], 200);
    }
}
