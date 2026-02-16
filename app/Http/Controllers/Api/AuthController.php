<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login and issue API token.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->only('email', 'password'), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        if (! Auth::guard('web')->attempt($validator->validated())) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = Auth::guard('web')->user();
        $user->tokens()->where('name', 'api')->delete();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Authenticated.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * Revoke current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * Return current user (same as GET /api/user, for consistency).
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->only(['id', 'name', 'email', 'email_verified_at']));
    }
}
