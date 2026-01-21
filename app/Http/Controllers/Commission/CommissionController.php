<?php

namespace App\Http\Controllers\Commission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commission;

class CommissionController extends Controller
{
    public function index()
    {
        return Commission::with('category')->get();
    }

    public function store(Request $request)
    {
        return Commission::create(
            $request->validate([
                'rule_code' => 'required|string|unique:category_commissions,rule_code',
                'category_id' => 'required|exists:service_categories,id',
                'commission_type' => 'required|in:percentage,flat',
                'value' => 'required|numeric',
                'status' => 'boolean'
            ])
        );
    }

    public function update(Request $request, Commission $commission)
    {
        $commission->update(
            $request->validate([
                'commission_type' => 'required|in:percentage,flat',
                'value' => 'required|numeric',
                'status' => 'boolean'
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
