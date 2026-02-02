<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Worker;
use App\Models\User;
use App\Services\CommissionService;

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
       
        // $booking = Booking::where('id', $data['booking_id'])
        //     ->whereIn('status', ['pending','reassigned'])
        //     ->firstOrFail();

        $booking = Booking::findOrFail($data['booking_id']);

        if (! in_array($booking->status, ['pending','reassigned'])) {
            return response()->json([
                'message' => 'Booking cannot be assigned in current status',
                'status'  => $booking->status
            ], 422);
        }

        $worker = User::findOrFail($data['worker_id']);

        $booking->update([
            'worker_id'  => $worker->id,
            'status'     => 'confirmed',
            'assigned_by'=> auth()->id(),
            'assigned_at'=> now(),
        ]);
        
        // $worker->update([
        //     'is_assigned' => true
        // ]);

        // OPTIONAL: notify worker (WhatsApp / push)
        // event(new BookingAssigned($booking));
        CommissionService::calculate($booking);
        
        return response()->json([
            'message' => 'Booking assigned successfully',
            'booking' => $booking->load('worker')
        ]);
    }
}
