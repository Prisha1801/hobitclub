<?php
namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Worker;

class WorkerProfileController extends Controller
{
    /**
     * Update worker profile
     */
    public function update(Request $request)
    {
        $worker = auth()->user()->worker;

        $request->validate([
            'skills'         => 'array',
            'preferred_area' => 'string|nullable',
            'available_days' => 'array|nullable',
            'available_time' => 'array|nullable',
        ]);

        $worker->update($request->only([
            'skills',
            'preferred_area',
            'available_days',
            'available_time',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
        ]);
    }

    /**
     * Get own profile (worker + user data)
     */
    public function me()
    {
        $user = auth()->user()->load('worker');

        if (! $user->worker) {
            return response()->json([
                'success' => false,
                'message' => 'Worker profile not found',
            ], 404);
        }

        return response()->json([
            'id'               => $user->worker->id,
            'user_id'          => $user->id,

            // 👤 USER DATA
            'name'             => $user->name,
            'phone'            => $user->phone,
            'email'            => $user->email,

            // 👷 WORKER DATA
            'service_category' => $user->worker->service_category,
            'wallet_balance'   => $user->worker->wallet_balance,
            'kyc_status'       => $user->worker->kyc_status,

            'id_type'          => $user->worker->id_type,
            'id_number'        => $user->worker->id_number,
            'id_front_url'     => $user->worker->id_front_path
                ? asset('storage/' . $user->worker->id_front_path)
                : null,
            'id_back_url'      => $user->worker->id_back_path
                ? asset('storage/' . $user->worker->id_back_path)
                : null,

            'skills'           => $user->worker->skills,
            'preferred_area'   => $user->worker->preferred_area,
            'available_days'   => $user->worker->available_days,
            'available_time'   => $user->worker->available_time,

            'created_at'       => $user->worker->created_at,
            'updated_at'       => $user->worker->updated_at,
        ]);
    }

    public function uploadKyc(Request $request)
    {
        $worker = auth()->user()->worker;

        if (! $worker) {
            return response()->json([
                'success' => false,
                'message' => 'Worker profile not found',
            ], 404);
        }

        $request->validate([
            'id_type'   => 'required|string',
            'id_number' => 'required|string',
            'id_front'  => 'required|image|mimes:jpg,jpeg,png',
            'id_back'   => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        // Base folder: public/uploads/workers/{worker_id}
        $basePath = "uploads/workers/{$worker->id}";

        // Store files
        $frontFileName = 'id_front_' . time() . '.' . $request->file('id_front')->getClientOriginalExtension();
        $frontPath     = $request->file('id_front')->storeAs(
            $basePath,
            $frontFileName,
            'public'
        );

        $backPath = null;
        if ($request->hasFile('id_back')) {
            $backFileName = 'id_back_' . time() . '.' . $request->file('id_back')->getClientOriginalExtension();
            $backPath     = $request->file('id_back')->storeAs(
                $basePath,
                $backFileName,
                'public'
            );
        }

        // Update worker KYC data
        $worker->update([
            'id_type'       => $request->id_type,
            'id_number'     => $request->id_number,
            'id_front_path' => $frontPath,
            'id_back_path'  => $backPath,
            'kyc_status'    => 'pending',
        ]);

        return response()->json([
            'success'   => true,
            'message'   => 'KYC uploaded successfully. Waiting for approval.',
            'documents' => [
                'id_front_url' => asset('storage/' . $frontPath),
                'id_back_url'  => $backPath ? asset('storage/' . $backPath) : null,
            ],
        ]);
    }

    public function uploaddocs(Request $request, Worker $worker = null)
    {
        if (auth()->user()->role === 'admin') {
            $worker = $worker ?? abort(404);
        } else {
            $worker = auth()->user()->worker;
        }

        $request->validate([
            'id_type'     => 'required|array|min:1',
            'id_type.*'   => 'required|string',

            'id_number'   => 'required|array|min:1',
            'id_number.*' => 'required|string',

            'id_front'    => 'required|array|min:1',
            'id_front.*'  => 'required|image|mimes:jpg,jpeg,png|max:2048',

            'id_back'     => 'nullable|array',
            'id_back.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $basePath = "uploads/workers/{$worker->id}";

        $idTypes     = [];
        $idNumbers   = [];
        $frontPaths  = [];
        $backPaths   = [];

        foreach ($request->id_type as $index => $type) {

            // Front image (mandatory)
            $frontPaths[] = $request->file('id_front')[$index]
                ->store($basePath, 'public');

            // Back image (optional)
            $backPaths[] = !empty($request->file('id_back')[$index])
                ? $request->file('id_back')[$index]->store($basePath, 'public')
                : null;

            $idTypes[]   = $type;
            $idNumbers[] = $request->id_number[$index];
        }

        $worker->update([
            'id_type'        => $idTypes,
            'id_number'      => $idNumbers,
            'id_front_path'  => $frontPaths,
            'id_back_path'   => $backPaths,
            'kyc_status'     => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'KYC documents updated successfully',
            'count'   => count($idTypes),
        ]);
    }
}
