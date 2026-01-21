<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtraTimeFee;
use Illuminate\Http\Request;

class ExtraTimeFeeController extends Controller
{
    public function index()
    {
        return ExtraTimeFee::with('service')->get();
    }

    public function store(Request $request)
    {
        return ExtraTimeFee::create($request->validate([
            'service_id'   => 'required|exists:services,id',
            'minutes'      => 'required|integer',
            'fee'          => 'required|numeric',
            'festival_fee' => 'nullable|numeric',
            'is_active'    => 'boolean'
        ]));
    }

    public function update(Request $request, ExtraTimeFee $extraTimeFee)
    {
        $extraTimeFee->update($request->validate([
            'minutes'      => 'required|integer',
            'fee'          => 'required|numeric',
            'festival_fee' => 'nullable|numeric',
            'is_active'    => 'boolean'
        ]));

        return response()->json(['message' => 'Extra time fee updated']);
    }

    public function destroy(ExtraTimeFee $extraTimeFee)
    {
        $extraTimeFee->delete();
        return response()->json(['message' => 'Extra time fee deleted']);
    }
}
