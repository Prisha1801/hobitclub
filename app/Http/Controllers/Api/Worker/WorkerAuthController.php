<?php

namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerAvailability;
use App\Models\Service;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WorkerAuthController extends Controller
{
    /**
     * REGISTER WORKER
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string',
            'email'    => 'nullable|email|unique:users,email',
            'phone'    => 'required|string|unique:users,phone',
            'password' => 'required|min:6',

            'category_ids'   => 'required|array|min:1',
            'category_ids.*' => 'exists:service_categories,id',

            'service_ids'   => 'required|array|min:1',
            'service_ids.*' => 'exists:services,id',

            'city_id' => 'nullable|exists:cities,id',
            'zone_id' => 'nullable|exists:zones,id',
            'area_id' => 'nullable|exists:serviceable_areas,id',

            'available_dates'   => 'required|array|min:1',
            'available_dates.*' => 'date',

            'available_times'         => 'required|array|min:1',
            'available_times.*.start' => 'required|date_format:H:i',
            'available_times.*.end'   => 'required|date_format:H:i',
            'is_active' => 'sometimes|boolean',
        ]);

        // Validate service-category relation
        $invalidServices = Service::whereIn('id', $request->service_ids)
            ->whereNotIn('category_id', $request->category_ids)
            ->exists();

        if ($invalidServices) {
            throw ValidationException::withMessages([
                'service_ids' => 'One or more services do not belong to selected categories'
            ]);
        }

        DB::beginTransaction();

        try {
            $workerRoleId = Role::where('slug', 'workers')->value('id');

            $user = User::create([
                'name'         => $request->name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'password'     => Hash::make($request->password),
                'role_id'      => $workerRoleId,
                'category_ids' => $request->category_ids,
                'service_ids'  => $request->service_ids,
                'city_id'      => $request->city_id,
                'zone_id'      => $request->zone_id,
                'area_id'      => $request->area_id,
                'is_active'    => $request->input('is_active', 0),
            ]);

            $worker = Worker::create([
                'user_id'    => $user->id,
                'kyc_status' => 'pending',
            ]);

            WorkerAvailability::create([
                'worker_id'       => $worker->user_id,
                'available_dates' => $request->available_dates,
                'available_times' => $request->available_times,
                'status'          => true,
            ]);

            DB::commit();

            return response()->json([
                'success'   => true,
                'worker_id' => $worker->id,
                'token'     => $user->createToken('worker-token')->plainTextToken,
                'message'   => 'Worker registered successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * UPDATE WORKER
     */
    public function update(Request $request, User $user = null)
    {
        if (auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('co-ordinators') || auth()->user()->hasRole('operation-head')) {
            $user = $user ?? abort(404, 'Worker not found');
        } else {
            $user = auth()->user();
        }

        if (!$user->hasRole('workers')) {
            abort(403, 'Target user is not a worker');
        }

        $request->validate([
            'name'     => 'sometimes|string',
            'email'    => 'sometimes|email|unique:users,email,' . $user->id,
            'phone'    => 'sometimes|string|unique:users,phone,' . $user->id,
            'password' => 'sometimes|min:6',

            'category_ids'   => 'sometimes|array|min:1',
            'category_ids.*' => 'exists:service_categories,id',

            'service_ids'   => 'sometimes|array|min:1',
            'service_ids.*' => 'exists:services,id',

            'city_id' => 'nullable|exists:cities,id',
            'zone_id' => 'nullable|exists:zones,id',
            'area_id' => 'nullable|exists:serviceable_areas,id',

            'available_dates'   => 'sometimes|array|min:1',
            'available_dates.*' => 'date',

            'available_times'         => 'sometimes|array|min:1',
            'available_times.*.start' => 'required_with:available_times|date_format:H:i',
            'available_times.*.end'   => 'required_with:available_times|date_format:H:i',
            'is_active' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();

        try {
            $user->update($request->only([
                'name',
                'email',
                'phone',
                'category_ids',
                'service_ids',
                'city_id',
                'zone_id',
                'area_id',
                'is_active',
            ]));

            if ($request->filled('password')) {
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
            }
            
            if ($request->has('available_dates') || $request->has('available_times')) {
                WorkerAvailability::updateOrCreate(
                    ['worker_id' => $user->id],
                    [
                        'available_dates' => $request->available_dates,
                        'available_times' => $request->available_times,
                        'status' => true,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Worker updated successfully',
                'data' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE WORKER
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->hasRole('super-admin') && auth()->id() !== $user->id) {
        //if (!auth()->user()->isAdmin() && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized');
        }

        if (!$user->hasRole('workers')) {
            abort(403, 'Target user is not a worker');
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Worker deleted successfully'
        ]);
    }
}
