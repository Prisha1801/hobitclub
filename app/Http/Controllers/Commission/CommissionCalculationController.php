<?php

namespace App\Http\Controllers\Commission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommissionCalculate;

class CommissionCalculationController extends Controller
{
    public function index()
    {
        $commissions = CommissionCalculate::with(['booking', 'worker', 'service'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'message' => 'commissions Review successfully',
            'commissions' => $commissions
        ]);
    }
}
