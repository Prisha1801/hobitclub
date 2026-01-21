<?php

namespace App\Http\Controllers;

use App\Models\BookingRating;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingRatingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id'  => 'required|exists:orders,id',
            'rating'      => 'required|integer|min:1|max:5',
            'description' => 'nullable|string'
        ]);

        $booking = Booking::where('id', $data['booking_id'])
            ->where('customer_id', auth()->id())
            ->where('status', 'completed')
            ->firstOrFail();

        $rating = BookingRating::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'worker_id'  => $booking->worker_id,
                'user_id'    => auth()->id(),
                'rating'     => $data['rating'],
                'description'=> $data['description']
            ]
        );

        return response()->json([
            'message' => 'Rating submitted successfully',
            'rating'  => $rating
        ]);
    }
}
