<?php

namespace App\Http\Controllers\Commission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commission;

class CommissionController extends Controller
{
    public function index()
    {
        return Commission::with('service')->get();
    }

    public function store(Request $request)
    {
        return Commission::create(
            $request->validate([
                'service_id' => 'required|exists:services,id',
                'commission_type' => 'required|in:percentage,fixed',
                'value' => 'required|numeric',
                'status' => 'sometimes|in:active,inactive'
            ])
        );
    }

    public function update(Request $request, Commission $commission)
    {
        $commission->update(
            $request->validate([
                'commission_type' => 'sometimes|in:percentage,fixed',
                'value' => 'sometimes|numeric',
                'status' => 'sometimes|in:active,inactive'
            ])
        );

        return response()->json(['message' => 'Commission rule updated']);
    }

    public function destroy(Commission $commission)
    {
        $commission->delete();
        return response()->json(['message' => 'Commission rule deleted']);
    }
}
