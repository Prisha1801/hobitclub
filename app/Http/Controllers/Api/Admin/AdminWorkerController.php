<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;

class AdminWorkerController extends Controller
{
    public function approveKyc(Worker $worker)
    {
        $worker->update([
            'kyc_status' => 'approved'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Worker KYC approved'
        ]);
    }

    public function rejectKyc(Worker $worker)
    {
        $worker->update([
            'kyc_status' => 'rejected'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Worker KYC rejected'
        ]);
    }
}
