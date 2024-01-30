<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\JWTException;

class RefreshToken extends Controller
{
    public function refresh(Request $request)
    {
        try {
            // Get the user's email from the request
            $email = $request->input('email');

            // Find the user by their email
            $user = User::where('email', $email)->first();

            // Check if the user exists
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            // Generate a new token for the user
            $newToken = auth()->tokenById($user->id);

            return $this->respondWithToken($newToken);
        } catch (JWTException $e) {
            // Log the error message for debugging
            Log::error('Could not generate a new token: ' . $e->getMessage());

            // Something went wrong whilst attempting to generate a new token
            return response()->json(['error' => 'Could not generate a new token'], 500);
        }
        // return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        $cookie = Cookie::make('token', $token, 1440, null, null, true, true);

        $ttl = auth()->factory()->getTTL(); // Get the TTL value
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
