<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserPassword;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'checkToken']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
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
    
        $token = Auth::login($user);
        return response()->json([
            'status' => 'success',
            'userData' => $user,
            'accessToken'=> $token,
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

    public function checkToken(Request $request)
    {
        try {
            $user = Auth::user();

            if ($user) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Token is valid',
                    'user' => $user,
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired token',
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while verifying the token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
