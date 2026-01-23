<?php
namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\CancelUnpaidBookingJob;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WhatsappBookingWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Log webhook
        DB::table('whatsapp_webhooks')->insert([
            'from'       => $request->input('from'),
            'flow_token' => $request->input('flow_token'),
            'payload'    => json_encode($request->all()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Validate payload
        $validator = Validator::make($request->all(), [
            'from'              => 'required|string',
            'data.service_id'   => 'required|integer',
            'data.booking_date' => 'required|date',
            'data.time_slot'    => 'required|string',
            'data.address'      => 'required|string',
            'data.amount'       => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $from = $request->input('from');
        $data = $request->input('data');

        // 3. Customer auto-create
        $customer = User::firstOrCreate(
            ['phone' => $from],
            [
                'name'      => 'WhatsApp Customer',
                'password'  => bcrypt(Str::random(12)),
                'role'      => 'customer',
                'is_active' => true,
            ]
        );

        // 4. Prevent slot double-booking
        $slotTaken = Booking::where('booking_date', $data['booking_date'])
            ->where('time_slot', $data['time_slot'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($slotTaken) {
            return response()->json([
                'success' => false,
                'message' => 'Slot already booked',
            ], 409);
        }

        // 5. Create new booking
        $booking = Booking::create([
            'customer_id'    => $customer->id,
            'service_id'     => $data['service_id'],
            'booking_date'   => $data['booking_date'],
            'time_slot'      => $data['time_slot'],
            'address'        => $data['address'],
            'amount'         => $data['amount'],
            'status'         => 'pending',
            'payment_status' => 'unpaid',
        ]);

        // 6. Dispatch auto-cancel job (15 minutes)
        CancelUnpaidBookingJob::dispatch($booking->id)
            ->delay(now()->addMinutes(15));

        // 7. Send payment request
        $this->sendUpiPaymentMessage($customer->phone, $booking);

        return response()->json([
            'success'    => true,
            'booking_id' => $booking->id,
            'message'    => 'Booking created. Payment requested.',
        ]);
    }

    private function sendUpiPaymentMessage(string $phone, Booking $booking): void
    {
        $upiLink = "upi://pay?pa=sonsakhi@upi&pn=Sonsakhi%20Collections&am={$booking->amount}&cu=INR&tn=Booking%20{$booking->id}";

        DB::table('whatsapp_payment_requests')->insert([
            'booking_id' => $booking->id,
            'upi_link'   => $upiLink,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Actual WhatsApp Cloud API call goes here
    }

    public function confirmPayment(Request $request)
    {
        $replyId = $request->input('reply_id');

        if (! str_starts_with($replyId, 'paid_')) {
            return response()->json(['message' => 'Invalid reference'], 400);
        }

        $bookingId = str_replace('paid_', '', $replyId);
        $booking   = Booking::find($bookingId);

        if (! $booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if ($booking->payment_status !== 'unpaid') {
            return response()->json(['message' => 'Already processed'], 200);
        }

        $booking->update([
            'payment_status' => 'verification_pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment marked for admin verification',
        ]);
    }
}
