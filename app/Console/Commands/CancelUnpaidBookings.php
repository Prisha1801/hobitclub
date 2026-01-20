<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;

class CancelUnpaidBookings extends Command
{
    protected $signature = 'bookings:cancel-unpaid';
    protected $description = 'Cancel unpaid bookings after 15 minutes';

    public function handle()
    {
        $count = Booking::where('payment_status', 'unpaid')
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(15))
            ->update(['status' => 'cancelled']);

        $this->info("Cancelled {$count} unpaid bookings.");
    }
}
