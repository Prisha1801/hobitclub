<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Worker;

class BookingAssignmentController extends Controller
{
     /**
     * Assign booking to worker
     */
    public function assign(Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'worker_id'  => 'required|exists:users,id'
        ]);

        $booking = Booking::where('id', $data['booking_id'])
            ->whereIn('status', ['pending','reassigned'])
            ->firstOrFail();

        $worker = User::findOrFail($data['worker_id']);

        $booking->update([
            'worker_id'  => $worker->id,
            'status'     => 'assigned',
            'assigned_by'=> auth()->id(),
            'assigned_at'=> now(),
        ]);
        
        $worker->update([
            'is_assigned' => true
        ]);

        // OPTIONAL: notify worker (WhatsApp / push)
        // event(new BookingAssigned($booking));

        return response()->json([
            'message' => 'Booking assigned successfully',
            'booking' => $booking->load('worker')
        ]);
    }
}
