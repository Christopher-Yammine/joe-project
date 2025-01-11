<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserPassword;
use App\Models\PasswordReset;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'requestReset', 'changePassword']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'rememberMe' => 'required|boolean'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $hashedPassword = UserPassword::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->value('hashed_password');

        if (!Hash::check($request->password, $hashedPassword)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }
        
        if ($request->rememberMe) {
            Auth::factory()->setTTL(60 * 24 * 30);
        } else {
            Auth::factory()->setTTL(60);
        }

        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'user' => $user->makeHidden(['created_at', 'updated_at'])->toArray() + [
                'role' => 'admin',
                'username' => 'admin',
            ],
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60,
            ]
        ]);
    }


    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
        ]);
        UserPassword::insert([
            'user_id' => $user->id,
            'hashed_password' => Hash::make($request->password),
            'created_at' => now(),
            'deleted_at' => null
        ]);
        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }


    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function validateToken()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
        ], 401);
    }

    return response()->json([
        'status' => 'success',
        'user' => $user,
    ]);
}

    public function requestReset(Request $request) {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $token = Str::random(60);

        PasswordReset::where('user_id', $user->id)->delete();

        PasswordReset::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        $resetUrl = env('RESET_PASSWORD_URL') . "?token=$token&email=" . urlencode($user->email);

        Mail::send('emails.reset-password', ['user' => $user, 'resetUrl' => $resetUrl], function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Password Reset Request');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Reset token sent.',
        ]);
    }

    public function changePassword(Request $request) {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        $passwordReset = PasswordReset::where('token', $request->token)
            ->where('expires_at', '>', Carbon::now())
            ->first();
            
        if (!$passwordReset) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token',
            ], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $userPassword = UserPassword::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->latest()
            ->first();

        if ($userPassword) {
            $userPassword->update([
                'deleted_at' => now(),
            ]);
        }

        UserPassword::create([
            'user_id' => $user->id,
            'hashed_password' => Hash::make($request->password),
            'created_at' => now(),
            'deleted_at' => null
        ]);

        $passwordReset->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully',
        ]);
    }
    
}
