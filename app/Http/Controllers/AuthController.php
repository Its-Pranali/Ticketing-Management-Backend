<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AuthController extends Controller
{
    private function getRoleName($id)
    {
        $role = Role::select('role')->where('id', $id)->first();
        return $role ? $role->role : "Unknown";
    }

    public function login(Request $request)
    {
        $email    = $request->input('email');
        $password = $request->input('password');
        // Find user by email first
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Invalid credentials', 'code' => 400]);
        }
        $user->role_name = $this->getRoleName($user->role);

        $tokenResult = $user->createToken('login-token');
        $accessToken = $tokenResult->accessToken;

        $csrfToken = \Illuminate\Support\Str::random(40);
        $authCookie = cookie(
            'auth_accessToken',
            $accessToken,
            60 * 24,
            '/',
            null,
            false, // secure
            true,  // httpOnly
            false, // raw
            'Lax'
        );

        $csrfCookie = cookie(
            'XSRF-TOKEN',
            $csrfToken,
            60 * 24,
            '/',
            null,
            false, // secure
            false, // httpOnly (must be false so JS can read it)
            false, // raw
            'Lax'
        );

        return response()->json([
            'status'     => true,
            'user'       => $user,
            'token'      => $accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expires_at->format('Y-m-d H:i:s'),
        ])->withCookie($authCookie)->withCookie($csrfCookie);
    }
}
