<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ServiceCategory;
use App\Models\Service;


class WorkerController extends Controller
{
    // public function index()
    // {
    //     return User::where('role', 'worker')
    //         ->with('worker',
    //             'worker_availablillity',
    //             'category:id,name',
    //             'service:id,name',
    //             'city:id,name',
    //             'zone:id,name',
    //             'area:id,name')
    //         ->latest()
    //         ->paginate(20);
        
    // }

    public function index()
    {
        $users = User::where('role', 'worker')
            ->with([
                'worker',
                'worker_availablillity',
                'city:id,name',
                'zone:id,name',
                'area:id,name',
            ])
            ->latest()
            ->paginate(20);

        $users->getCollection()->transform(function ($user) {

            $worker = $user->worker;

            /* ------------------------------
            Categories & Services
            ------------------------------ */
            $categoryIds = $this->normalizeIds($user->category_ids);
            $serviceIds  = $this->normalizeIds($user->service_ids);

            $categories = empty($categoryIds)
                ? []
                : ServiceCategory::whereIn('id', $categoryIds)
                    ->select('id', 'name')
                    ->get()
                    ->toArray();

            $services = empty($serviceIds)
                ? []
                : Service::whereIn('id', $serviceIds)
                    ->select('id', 'name')
                    ->get()
                    ->toArray();

            /* ------------------------------
            KYC Documents
            ------------------------------ */
            $documents = [];

            if ($worker && is_array($worker->id_type)) {
                foreach ($worker->id_type as $index => $type) {
                    $documents[] = [
                        'type' => $type,
                        'number' => $worker->id_number[$index] ?? null,
                        'front_url' => isset($worker->id_front_path[$index])
                            ? asset('storage/' . $worker->id_front_path[$index])
                            : null,
                        'back_url' => isset($worker->id_back_path[$index])
                            ? asset('storage/' . $worker->id_back_path[$index])
                            : null,
                    ];
                }
            }

            /* ------------------------------
            FINAL SHAPED RESPONSE
            ------------------------------ */
            return [
                'id'           => $user->id,
                'worker_id'    => $worker?->id,

                'name'         => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'is_active'    => $user->is_active,
                'is_assigned'  => $user->is_assigned,

                'categories'   => $categories,
                'services'     => $services,

                'city'         => $user->city,
                'zone'         => $user->zone,
                'area'         => $user->area,

                'wallet_balance' => $worker?->wallet_balance,
                'kyc_status'     => $worker?->kyc_status,

                'documents'      => $documents,

                'worker_availablillity' => $user->worker_availablillity,
            ];
        });

        return $users;
    }



    private function normalizeIds($value): array
    {
        if (empty($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (is_array($value) && isset($value[0]) && is_array($value[0])) {
            $value = $value[0];
        }

        if (!is_array($value)) {
            return [];
        }

        // 🔥 FORCE INTEGER + REMOVE EMPTY
        return array_values(
            array_filter(
                array_map('intval', $value)
            )
        );
    }

    public function unassigned_worker()
    {
        return User::where('role', 'worker')
            ->with('worker',
                'worker_availablillity',
                'category:id,name',
                'service:id,name',
                'city:id,name',
                'zone:id,name',
                'area:id,name')
            ->latest()
            ->where(function ($q) {
                $q->whereNull('is_assigned')
                ->orWhere('is_assigned', false);
            })
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'phone' => 'required|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'role' => 'worker'
        ]);

        $user->worker()->create([
            'service_category' => $request->service_category
        ]);

        return response()->json(['message' => 'Worker created']);
    }

    public function updateStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return response()->json(['message' => 'Status updated']);
    }
}

