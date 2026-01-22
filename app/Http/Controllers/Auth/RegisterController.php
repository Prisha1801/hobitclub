<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Worker;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'phone'    => 'required|string|max:15|unique:users,phone',
            'email'    => 'nullable|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => [
                'required',
                Rule::in([
                    'admin',
                    'coordinator',
                    'operation_head',
                    'worker',
                    'customer',
                    'staff'
                ])
            ],
        ]);

        $user = User::create([
            'name'      => $request->name,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'is_active' => true
        ]);

        // Auto create role-specific profile
        if ($user->role === 'worker') {
            Worker::create([
                'user_id' => $user->id
            ]);
        }

        if ($user->role === 'customer') {
            Customer::create([
                'user_id' => $user->id
            ]);
        }

        return response()->json([
            'message' => 'Registration successful',
            'token'   => $user->createToken('api-token')->plainTextToken,
            'user'    => $user
        ], 201);
    }
}
