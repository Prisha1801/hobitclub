<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WorkerAuthController extends Controller
{
    /**
     * Create worker account and generate token
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => 'worker',
            'is_active' => true,
        ]);

        $worker = Worker::create([
            'user_id'    => $user->id,
            'kyc_status' => 'pending',
        ]);

        // ✅ GENERATE SANCTUM TOKEN
        $token = $user->createToken('worker-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'worker_id' => $worker->id,
            'token' => $token,
            'message' => 'Worker registered. Complete profile & KYC.'
        ]);
    }
}
