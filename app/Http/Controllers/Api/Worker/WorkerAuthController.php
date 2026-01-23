<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerAvailability;
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
            'category_id' => 'nullable|exists:service_categories,id',
            'service_id' => 'nullable|exists:services,id',
            'city_id' => 'nullable|exists:cities,id',
            'zone_id' => 'nullable|exists:zones,id',
            'area_id' => 'nullable|exists:serviceable_areas,id',
            
            'available_dates' => 'required|array|min:1',
            'available_dates.*' => 'date',

            'available_times' => 'required|array|min:1',
            'available_times.*.start' => 'required|date_format:H:i',
            'available_times.*.end'   => 'required|date_format:H:i',
        ]);
        
        $user = User::create([
            'name'      => $request->name,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'role'      => 'worker',
            'category_id' => $request->category_id,
            'service_id' => $request->service_id,
            'city_id' => $request->city_id,
            'zone_id' => $request->zone_id,
            'area_id' => $request->area_id,
            'is_active' => true,
        ]);

        $worker = Worker::create([
            'user_id'    => $user->id,
            'kyc_status' => 'pending',
        ]);

        WorkerAvailability::create([
            'worker_id'       => $worker->id,
            'available_dates' => $request->available_dates,
            'available_times' => $request->available_times,
            'status'          => true
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
