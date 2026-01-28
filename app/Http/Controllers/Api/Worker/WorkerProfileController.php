<?php
namespace App\Http\Controllers\Api\Worker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Worker;
use App\Models\Service;
use App\Models\ServiceCategory;

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
        $user = auth()->user()->load([
            'worker',
            'worker_availablillity',
            'city:id,name',
            'zone:id,name',
            'area:id,name',
        ]);

        if (! $user->worker) {
            return response()->json([
                'success' => false,
                'message' => 'Worker profile not found',
            ], 404);
        }

        $worker = $user->worker;

        /* ----------------------------------------
        Categories & Services (from users table)
        ----------------------------------------- */
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

        /* ----------------------------------------
        KYC Documents (formatted)
        ----------------------------------------- */
        $documents = [];

        if (is_array($worker->id_type)) {
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

        /* ----------------------------------------
        FINAL RESPONSE (FLAT & FRONTEND READY)
        ----------------------------------------- */
        return response()->json([
            'id'           => $user->id,
            'worker_id'    => $worker->id,

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

            'wallet_balance' => $worker->wallet_balance,
            'kyc_status'     => $worker->kyc_status,

            'documents'      => $documents,

            'worker_availablillity' => $user->worker_availablillity,
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

    // public function uploaddocs(Request $request, User $user = null)
    // {
        
    //     if (auth()->user()->role === 'admin') {
    //         $worker = $user->worker;
    //     } else {
    //         $worker = auth()->user()->worker;
    //     }
    //     if (! $worker) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Worker profile not found',
    //         ], 404);
    //     }
    //     $request->validate([
    //         'id_type'     => 'required|array|min:1',
    //         'id_type.*'   => 'required|string',

    //         'id_number'   => 'required|array|min:1',
    //         'id_number.*' => 'required|string',

    //         'id_front'    => 'required|array|min:1',
    //         'id_front.*'  => 'required|image|mimes:jpg,jpeg,png|max:2048',

    //         'id_back'     => 'nullable|array',
    //         'id_back.*'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //     ]);

    //     $basePath = "uploads/workers/{$worker->id}";

    //     $idTypes     = [];
    //     $idNumbers   = [];
    //     $frontPaths  = [];
    //     $backPaths   = [];

    //     foreach ($request->id_type as $index => $type) {

    //         // Front image (mandatory)
    //         $frontPaths[] = $request->file('id_front')[$index]
    //             ->store($basePath, 'public');

    //         // Back image (optional)
    //         $backPaths[] = !empty($request->file('id_back')[$index])
    //             ? $request->file('id_back')[$index]->store($basePath, 'public')
    //             : null;

    //         $idTypes[]   = $type;
    //         $idNumbers[] = $request->id_number[$index];
    //     }

    //     $worker->update([
    //         'id_type'        => $idTypes,
    //         'id_number'      => $idNumbers,
    //         'id_front_path'  => $frontPaths,
    //         'id_back_path'   => $backPaths,
    //         'kyc_status'     => 'pending',
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'KYC documents updated successfully',
    //         'count'   => count($idTypes),
    //     ]);
    // }

    public function uploaddocs(Request $request, User $user = null)
    {
        /* ------------------------------------
        Resolve worker (admin / worker)
        ------------------------------------- */
        if (auth()->user()->isAdmin()) {
            $worker = $user?->worker;
        } else {
            $worker = auth()->user()->worker;
        }

        if (! $worker) {
            return response()->json([
                'success' => false,
                'message' => 'Worker profile not found',
            ], 404);
        }

        /* ------------------------------------
        Validation
        ------------------------------------- */
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

        /* ------------------------------------
        Public path (Hostinger safe)
        public_html/storage/uploads/workers/{id}
        ------------------------------------- */
        $publicBasePath = public_path("storage/uploads/workers/{$worker->id}");

        if (! file_exists($publicBasePath)) {
            mkdir($publicBasePath, 0755, true);
        }

        $idTypes    = [];
        $idNumbers  = [];
        $frontPaths = [];
        $backPaths  = [];

        /* ------------------------------------
        Store documents
        ------------------------------------- */
        foreach ($request->id_type as $index => $type) {

            $safeType  = str_replace(' ', '_', strtolower($type));
            $timestamp = time();

            /* -------- FRONT IMAGE (required) -------- */
            $frontFile = $request->file('id_front')[$index];
            $frontFileName = "{$safeType}_front_{$timestamp}_{$index}."
                . $frontFile->getClientOriginalExtension();

            $frontFile->move($publicBasePath, $frontFileName);

            $frontRelativePath = "uploads/workers/{$worker->id}/{$frontFileName}";

            /* -------- BACK IMAGE (optional) -------- */
            $backRelativePath = null;

            if (isset($request->file('id_back')[$index])) {
                $backFile = $request->file('id_back')[$index];
                $backFileName = "{$safeType}_back_{$timestamp}_{$index}."
                    . $backFile->getClientOriginalExtension();

                $backFile->move($publicBasePath, $backFileName);

                $backRelativePath = "uploads/workers/{$worker->id}/{$backFileName}";
            }

            /* -------- Collect data -------- */
            $idTypes[]    = $type;
            $idNumbers[]  = $request->id_number[$index];
            $frontPaths[] = $frontRelativePath;
            $backPaths[]  = $backRelativePath;
        }

        /* ------------------------------------
        Update worker
        ------------------------------------- */
        $worker->update([
            'id_type'       => $idTypes,
            'id_number'     => $idNumbers,
            'id_front_path' => $frontPaths,
            'id_back_path'  => $backPaths,
            'kyc_status'    => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'KYC documents uploaded successfully',
            'documents_count' => count($idTypes),
        ]);
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
}