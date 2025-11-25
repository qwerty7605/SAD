<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(UserRequest $userRequest)
    {
        $validated = $userRequest->validated();

        $create = User::create($validated);

        return response()->json([
            'message' => 'register' ,
            'data' => $create
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please contact the administrator.'],
            ]);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Load relationships based on user type
        $userData = $user->toArray();
        if ($user->user_type === 'student') {
            $user->load('student');
            $userData['student'] = $user->student;
        } elseif ($user->user_type === 'org_admin') {
            $user->load('organizationAdmin.organization');
            $orgAdmin = $user->organizationAdmin;
            $userData['organization_admin'] = $orgAdmin;
            // Add convenient top-level fields for dashboard
            $userData['full_name'] = $orgAdmin->full_name ?? null;
            $userData['position'] = $orgAdmin->position ?? null;
            $userData['organization_name'] = $orgAdmin->organization->org_name ?? null;
            $userData['department'] = $orgAdmin->organization->department ?? null;
        } elseif ($user->user_type === 'sys_admin') {
            $user->load('systemAdmin');
            $userData['system_admin'] = $user->systemAdmin;
        }

        return response()->json([
            'token' => $token,
            'user' => $userData,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
