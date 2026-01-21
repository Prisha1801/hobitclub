<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionType;

class SubscriptionTypeController extends Controller
{
    public function index()
    {
        return SubscriptionType::all();
    }

    public function store(Request $request)
    {
        return SubscriptionType::create(
            $request->validate(['name' => 'required|string|max:255'])
        );
    }

    public function update(Request $request, SubscriptionType $subscriptionType)
    {
        $subscriptionType->update(
            $request->validate(['name' => 'required|string|max:255'])
        );

        return response()->json(['message' => 'Subscription updated']);
    }

    public function destroy(SubscriptionType $subscriptionType)
    {
        $subscriptionType->delete();
        return response()->json(['message' => 'Subscription deleted']);
    }
}
