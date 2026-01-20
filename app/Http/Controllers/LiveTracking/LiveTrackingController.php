<?php

namespace App\Http\Controllers\LiveTracking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveLocation;

class LiveTrackingController extends Controller
{
    public function updateLocation(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $location = LiveLocation::updateOrCreate(
            [
                'order_id' => $request->order_id,
                'worker_id' => auth()->id()
            ],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'updated_at' => now()
            ]
        );

        return response()->json([
            'tracking_url' => $this->generateTrackingLink(
                $location->latitude,
                $location->longitude
            )
        ]);
    }

    private function generateTrackingLink($lat, $lng)
    {
        return "https://www.google.com/maps?q={$lat},{$lng}";
    }
}
