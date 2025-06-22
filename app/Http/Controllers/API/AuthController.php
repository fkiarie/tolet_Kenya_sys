<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * User login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke existing tokens for this device (optional - for single device login)
        $user->tokens()->where('name', $request->device_name)->delete();

        // Define abilities based on user role or needs
        $abilities = $this->getUserAbilities($user);
        
        $token = $user->createToken($request->device_name, $abilities)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ],
            'token' => $token,
            'abilities' => $abilities,
            'token_type' => 'Bearer'
        ], 200);
    }

    /**
     * User registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'device_name' => 'required|string'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $abilities = $this->getUserAbilities($user);
        $token = $user->createToken($request->device_name, $abilities)->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ],
            'token' => $token,
            'abilities' => $abilities,
            'token_type' => 'Bearer'
        ], 201);
    }

    /**
     * User logout (current device)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();
        
        return response()->json([
            'message' => 'Successfully logged out from all devices'
        ], 200);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],
            'abilities' => $request->user()->currentAccessToken()->abilities ?? []
        ], 200);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'device_name' => 'required|string'
        ]);

        $user = $request->user();
        
        // Delete current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $abilities = $this->getUserAbilities($user);
        $token = $user->createToken($request->device_name, $abilities)->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $token,
            'abilities' => $abilities,
            'token_type' => 'Bearer'
        ], 200);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ], 200);
    }

    /**
     * Get user's active tokens
     */
    public function tokens(Request $request)
    {
        $tokens = $request->user()->tokens()->get(['id', 'name', 'abilities', 'last_used_at', 'created_at']);
        
        return response()->json([
            'tokens' => $tokens
        ], 200);
    }

    /**
     * Revoke specific token
     */
    public function revokeToken(Request $request)
    {
        $request->validate([
            'token_id' => 'required|integer|exists:personal_access_tokens,id'
        ]);

        $request->user()->tokens()->where('id', $request->token_id)->delete();

        return response()->json([
            'message' => 'Token revoked successfully'
        ], 200);
    }

    /**
     * Get user abilities based on role or other criteria
     */
    private function getUserAbilities(User $user): array
    {
        // You can customize this based on your needs
        // For example, check user roles, permissions, etc.
        
        return [
            'landlords:view',
            'landlords:manage',
            'buildings:view', 
            'buildings:manage',
            'units:view',
            'units:manage',
            'tenants:view',
            'tenants:manage',
        ];
        
        // Example with role-based abilities:
        /*
        if ($user->hasRole('admin')) {
            return ['*']; // All abilities
        } elseif ($user->hasRole('manager')) {
            return [
                'landlords:view', 'landlords:manage',
                'buildings:view', 'buildings:manage',
                'units:view', 'units:manage',
                'tenants:view', 'tenants:manage',
            ];
        } else {
            return [
                'landlords:view',
                'buildings:view',
                'units:view',
                'tenants:view',
            ];
        }
        */
    }
}