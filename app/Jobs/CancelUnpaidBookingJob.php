<?php

namespace App\Jobs;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CancelUnpaidBookingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $bookingId;

    public function __construct(int $bookingId)
    {
        $this->bookingId = $bookingId;
    }

    public function handle(): void
    {
        $booking = Booking::find($this->bookingId);

        // Booking removed or already finalized
        if (!$booking) {
            return;
        }

        /*
         * CANCEL ONLY IF:
         * - payment not initiated
         * - booking still pending
         *
         * DO NOT cancel if:
         * - verification_pending
         * - paid
         * - rejected
         */
        if (
            $booking->status === 'pending' &&
            $booking->payment_status === 'unpaid'
        ) {
            $booking->update([
                'status' => 'cancelled',
            ]);
        }
    }
}
