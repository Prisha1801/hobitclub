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
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'phone'     => $request->phone,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role_id'   => $request->role_id,
            'is_active' => true
        ]);

        if ($user->hasRole('worker')) {
            Worker::create(['user_id' => $user->id]);
        }

        if ($user->hasRole('customer')) {
            Customer::create(['user_id' => $user->id]);
        }

        return response()->json([
            'message' => 'Registration successful',
            'token'   => $user->createToken('api-token')->plainTextToken,
            'user'    => $user
        ], 201);
    }
}
