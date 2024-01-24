<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Faker\Generator as Faker;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }

    // Register a new user
    public function register(Request $request)
    {
        $credentials = $request->only('name', 'email', 'password');

        $request->validate([
            'name' => 'required|string|max:255',
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

        $cookie = Cookie::make('token', $token, 1440, null, null, true, true);

        return response()->json(compact('user', 'token'), 201)->withCookie($cookie);
    }

    // Login an existing user
    public function login(Request $request)
    {
        Log::info($request);
        $credentials = $request->only('email', 'password');

        // $credentials->validate([
        //     'email' => 'required|string|email',
        //     'password' => 'required|string',
        // ]);

        // if (Auth::attempt(['email' => $credentials->email, 'password' => $credentials->password])) {
        //     $user = Auth::user();
        //     $token = $user->createToken('authToken')->accessToken;

        //     return response()->json(['user' => $user, 'access_token' => $token], 200);
        // } else {
        //     return response()->json(['message' => 'Invalid credentials'], 401);
        // }

        $token = auth()->attempt($credentials);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    // Logout
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
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

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL(),
        ])->withCookie($cookie);
    }
}
