<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * 🔔 Notification count (bell badge)
     */
    public function count()
    {
        $count = Booking::where('is_seen', false)->count();

        return response()->json([
            'success' => true,
            'count'   => $count
        ]);
    }

    /**
     * 📋 Notification panel
     * When this API is called → count becomes 0
     */
    public function index()
    {
        $count = Booking::where('is_seen', false)->count();

        $notifications = Booking::with(['service', 'customer'])
            ->where('is_seen', false)
            ->latest()
            ->get()
            ->map(function ($booking) {

                $date = Carbon::parse($booking->booking_date);

                return [
                    'id' => $booking->id,
                    'title' => 'New Booking #' . $booking->id,
                    'message' =>
                        $booking->service->name .
                        ' request from ' .
                        $booking->customer->name,

                    'date_label' => $date->isToday()
                        ? 'Today'
                        : ($date->isTomorrow()
                            ? 'Tomorrow'
                            : $date->format('d M Y')),

                    'time_slot' => $booking->time_slot,
                ];
            });

        // ✅ Mark all as seen
        Booking::where('is_seen', false)->update(['is_seen' => true]);

        return response()->json([
            'success'   => true,
            'old_count'   => $count,
            'new_count' => 0,
            'data'      => $notifications
        ]);
    }
}
