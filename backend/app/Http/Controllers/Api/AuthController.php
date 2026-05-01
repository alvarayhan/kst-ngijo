<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class AuthController extends Controller
{
    public function login(Request $request)
    {   
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $token = Auth::guard('api')->attempt($credentials);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        $user = Auth::guard('api')->user();

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name ?? null,
                    'status' => $user->status,
                ]
            ]
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $token = JWTAuth::parseToken()->refresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed'
            ], 401);
        }
    }

    public function me(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name ?? null,
                'status' => $user->status,
                'created_at' => $user->created_at
            ]
        ]);
    }

    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,operator',
        ]);

        $role = Role::where('name', $request->role)->first();
        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid role'
            ], 400);
        }

        // Check if role already has a user
        $existingUser = User::where('role_id', $role->id)->exists();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Role ' . $request->role . ' already has a user. Only one user per role is allowed.'
            ], 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Signup successful. You can now login.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name,
                ]
            ]
        ], 201);
    }
}