<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class BookingApprovalController extends Controller
{
    public function approve(Request $request, Booking $booking)
    {
        // Only pending bookings can be approved
        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be approved.'
            ], 422);
        }

        // Optional: payment condition before approval
        // if ($booking->payment_status !== 'paid') {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Payment must be completed before approval.'
        //     ], 422);
        // }

        $booking->update([
            'status'       => 'confirmed',
            'approved_by'  => auth()->id(),
            'approved_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking approved successfully.',
            'data'    => [
                'booking_id' => $booking->id,
                'status'     => $booking->status,
                'approved_by'=> $booking->approved_by,
                'approved_at'=> $booking->approved_at,
            ]
        ]);
    }
}
