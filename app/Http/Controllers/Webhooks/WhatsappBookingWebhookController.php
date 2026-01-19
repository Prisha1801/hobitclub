<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WhatsappBookingWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Log raw webhook (debug / audit)
        DB::table('whatsapp_webhooks')->insert([
            'from'       => $request->input('from'),
            'flow_token' => $request->input('flow_token'),
            'payload'    => json_encode($request->all()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $from = $request->input('from');
        $data = $request->input('data');

        if (!$from || !$data) {
            return response()->json(['message' => 'Invalid webhook'], 400);
        }

        // Auto-create or fetch customer
        $customer = User::firstOrCreate(
            ['phone' => $from],
            [
                'name'     => 'WhatsApp Customer',
                'password' => bcrypt(Str::random(12)),
                'role'     => 'customer',
                'is_active'=> true,
            ]
        );

        // Create booking
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'service_id'  => $data['service_id'],
            'booking_date'=> $data['booking_date'],
            'time_slot'   => $data['time_slot'],
            'address'     => $data['address'],
            'status'      => 'pending',
        ]);

        // (Optional) Send WhatsApp confirmation here

        return response()->json([
            'message'    => 'Booking created successfully',
            'booking_id' => $booking->id
        ], 200);
    }
}
