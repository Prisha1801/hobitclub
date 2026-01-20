<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    public function approvePayment(Request $request, Booking $booking)
    {
        if ($booking->payment_status !== 'verification_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid booking state',
            ], 400);
        }

        $booking->update([
            'payment_status' => 'paid',
            'status'         => 'confirmed',
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
            'paid_at'        => now(),
        ]);

        // 🔔 TODO: Send WhatsApp payment-approved message

        return response()->json([
            'success' => true,
            'message' => 'Payment approved successfully',
        ]);
    }

    public function rejectPayment(Request $request, Booking $booking)
    {
        if ($booking->payment_status !== 'verification_pending') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid booking state',
            ], 400);
        }

        $booking->update([
            'payment_status' => 'rejected',
            'status'         => 'cancelled',
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
        ]);

        // 🔔 TODO: Send WhatsApp payment-rejected message

        return response()->json([
            'success' => true,
            'message' => 'Payment rejected successfully',
        ]);
    }
}
