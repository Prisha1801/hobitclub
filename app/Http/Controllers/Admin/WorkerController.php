<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use App\Models\ServiceCategory;
use App\Models\Service;
use Carbon\Carbon;

class WorkerController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();

        $workerRoleId = \App\Models\Role::where('slug', 'workers')->value('id');

        $query = User::where('role_id', $workerRoleId)
            ->with([
                'worker',
                'worker_availablillity',
                'city:id,name',
                'zone:id,name',
                'area:id,name',
                'addedBy:id,name,public_id', // 👈 important
            ]);

        /* ------------------------------------
        ROLE BASED FILTERING
        ------------------------------------ */
        if ($authUser->role->slug === 'agent') {
            $query->where('added_by', $authUser->id);
        }

        // super-admin, co-ordinators, operation-head → no filter

        $users = $query->latest()->paginate(20);

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
                    ->get();

            $services = empty($serviceIds)
                ? []
                : Service::whereIn('id', $serviceIds)
                    ->select('id', 'name')
                    ->get();

            /* ------------------------------
            KYC Documents
            ------------------------------ */
            $documents = [];

            if ($worker && is_array($worker->id_type)) {
                foreach ($worker->id_type as $index => $type) {
                    $documents[] = [
                        'type'       => $type,
                        'number'     => $worker->id_number[$index] ?? null,
                        'front_url'  => isset($worker->id_front_path[$index])
                            ? asset('storage/' . $worker->id_front_path[$index])
                            : null,
                        'back_url'   => isset($worker->id_back_path[$index])
                            ? asset('storage/' . $worker->id_back_path[$index])
                            : null,
                    ];
                }
            }

            /* ------------------------------
            FINAL RESPONSE
            ------------------------------ */
            return [
                'id'          => $user->id,
                'worker_id'   => $worker?->id,
                'public_id'   => $user->public_id,

                'added_by' => $user->addedBy ? [
                    'id'        => $user->addedBy->id,
                    'name'      => $user->addedBy->name,
                    'public_id' => $user->addedBy->public_id,
                ] : null,

                'name'        => $user->name,
                'email'       => $user->email,
                'phone'       => $user->phone,
                'is_active'   => $user->is_active,
                'is_assigned' => $user->is_assigned,

                'categories'  => $categories,
                'services'    => $services,

                'city'        => $user->city,
                'zone'        => $user->zone,
                'area'        => $user->area,

                'wallet_balance' => $worker?->wallet_balance,
                'kyc_status'     => $worker?->kyc_status,

                'documents' => $documents,

                'worker_availablillity' => $user->worker_availablillity,
            ];
        });

        return $users;
    }

    public function index1()
    {
        $workerRoleId = \App\Models\Role::where('slug', 'workers')->value('id');
        $users = User::where('role_id', $workerRoleId)
            ->with([
                'worker',
                'worker_availablillity',
                'city:id,name',
                'zone:id,name',
                'area:id,name',
                'addedBy:id,name,public_id',
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
                'public_id'    => $user->public_id,
                'added_by'     => $user->user,
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

    // public function unassigned_worker()
    // {   
    //     $workerRoleId = \App\Models\Role::where('slug', 'workers')->value('id');
    //     return User::where('role_id', $workerRoleId)
    //         ->with('worker',
    //             'worker_availablillity',
    //             'category:id,name',
    //             'service:id,name',
    //             'city:id,name',
    //             'zone:id,name',
    //             'area:id,name')
    //         ->latest()
    //         ->where(function ($q) {
    //             $q->whereNull('is_assigned')
    //             ->orWhere('is_assigned', false);
    //         })
    //         ->paginate(20);
    // }

    public function unassigned_worker(Booking $booking)
    {
        // Parse time slot safely
        try {
            [$bookingStart, $bookingEnd] = $this->parseTimeSlot($booking->time_slot);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid time slot format',
                'time_slot' => $booking->time_slot
            ], 422);
        }
    
        $bookingDate = $booking->booking_date;
        $serviceId   = (int) $booking->service_id;
    
        $workers = User::query()            
                ->whereJsonContains('service_ids', $serviceId)
                // ->whereHas('worker_availablillity', function ($q) use ($bookingDate, $bookingStart, $bookingEnd) {
                //     $q->whereJsonContains('available_dates', $bookingDate)
                //       ->whereRaw("
                //         EXISTS (
                //             SELECT 1
                //             FROM JSON_TABLE(
                //                 available_times,
                //                 '$[*]' COLUMNS (
                //                     start_time VARCHAR(5) PATH '$.start',
                //                     end_time   VARCHAR(5) PATH '$.end'
                //                 )
                //             ) t
                //             WHERE t.start_time < ?
                //             AND t.end_time   > ?
                //         )
                //       ", [$bookingEnd, $bookingStart]);
                // })
                ->whereDoesntHave('bookings', function ($q) use ($bookingDate, $bookingStart, $bookingEnd) {
                    $q->where('booking_date', $bookingDate)
                      ->whereNotIn('status', ['cancelled'])
                      ->where(function ($overlap) use ($bookingStart, $bookingEnd) {
                          $overlap->whereRaw("
                              SUBSTRING_INDEX(time_slot, '-', 1) < ?
                              AND SUBSTRING_INDEX(time_slot, '-', -1) > ?
                            ", [$bookingEnd, $bookingStart]);
                        });
                    })
    
                ->where('is_active', 1)
                ->select('id', 'name', 'phone')
                ->get();
    
        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'service_id' => $serviceId,
                'date' => $bookingDate,
                'time_slot' => $booking->time_slot,
            ],
            'available_workers' => $workers
        ]);
    }
    
    private function parseTimeSlot(string $timeSlot): array
    {
        // Normalize string
        $timeSlot = str_replace('_', ' ', $timeSlot);
        $timeSlot = str_replace('–', '-', $timeSlot); // en-dash → dash
    
        // Expected now: "09:00 AM - 12:00 PM"
        if (! str_contains($timeSlot, '-')) {
            throw new \Exception('Invalid time slot format');
        }
    
        [$start, $end] = array_map('trim', explode('-', $timeSlot));
    
        // Convert to 24-hour format
        $startTime = Carbon::createFromFormat('h:i A', $start)->format('H:i');
        $endTime   = Carbon::createFromFormat('h:i A', $end)->format('H:i');
    
        return [$startTime, $endTime];
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

