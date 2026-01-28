<?php
namespace App\Http\Controllers\Booking;

use App\Models\BookingRating;
use App\Models\Booking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BookingRatingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id'  => 'required|exists:bookings,id',
            'rating'      => 'required|integer|min:1|max:5',
            'description' => 'nullable|string'
        ]);

        $booking = Booking::where('id', $data['booking_id'])
            ->where('customer_id', auth()->id())
            ->where('status', 'completed')
            ->first();

        if (! $booking) {
            return response()->json([
                'message' => 'Booking not found or not eligible for rating'
            ], 404);
        }

        $rating = BookingRating::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'worker_id'  => $booking->worker_id,
                'customer_id' => auth()->id(),
                'rating'     => $data['rating'],
                'description'=> $data['description']
            ]
        );

        return response()->json([
            'message' => 'Rating submitted successfully',
            'rating'  => $rating
        ]);
    }

    public function show($bookingId)
    {
        $rating = BookingRating::where('booking_id', $bookingId)
            ->where('customer_id', auth()->id())
            ->with(['worker:id,name', 'booking:id'])
            ->firstOrFail();

        return response()->json([
            'data' => $rating
        ]);
    }

    public function myRatings()
    {
        $ratings = BookingRating::where('customer_id', auth()->id())
            ->with(['worker:id,name', 'booking:id'])
            ->latest()
            ->paginate(10);

        return response()->json($ratings);
    }

    public function workerRatings($workerId)
    {
        $ratings = BookingRating::where('worker_id', $workerId)
            ->with(['customer:id,name'])
            ->latest()
            ->paginate(10);

        return response()->json($ratings);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'rating'      => 'required|integer|min:1|max:5',
            'description' => 'nullable|string'
        ]);

        $rating = BookingRating::where('id', $id)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        $rating->update($data);

        return response()->json([
            'message' => 'Rating updated successfully',
            'data'    => $rating
        ]);
    }

    public function destroy($id)
    {
        $rating = BookingRating::where('id', $id)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        $rating->delete();

        return response()->json([
            'message' => 'Rating deleted successfully'
        ]);
    }
}
