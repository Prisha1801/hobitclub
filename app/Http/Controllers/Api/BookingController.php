<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * List Services (Bot / CRM)
     */
    public function services()
    {
        $services = Service::where('status', true)->get()->map(function ($service) {
            return [
                'id'               => $service->id,
                'category_id'      => $service->category_id,
                'name'             => $service->name,
                'description'      => $service->description,
                'duration_minutes' => $service->duration_minutes,
                'price'            => $this->calculatePrice($service),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $services
        ]);
    }

    /**
     * Create Booking (BOT → CRM)
     */
    public function bookingStore(Request $request)
    {
        $request->validate([
            'phone'          => 'required|string',
            'customer_name'  => 'required|string',
            'service_id'     => 'required|exists:services,id',
            'booking_date'   => 'required|date',
            'time_slot'      => 'required|string',
            'address'        => 'required|string',
            'city'           => 'nullable|string',
            'pincode'        => 'nullable|string',
            'latitude'       => 'nullable|string',
            'longitude'      => 'nullable|string',
            'source'         => 'nullable|string',
            'bot_session_id' => 'nullable|string',
        ]);

        // Customer create / fetch
        $customer = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'name'      => $request->customer_name,
                'password'  => bcrypt(Str::random(10)),
                'role'      => 'customer',
                'is_active' => true,
            ]
        );

        // Slot lock
        $slotTaken = Booking::where('booking_date', $request->booking_date)
            ->where('time_slot', $request->time_slot)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($slotTaken) {
            return response()->json([
                'success' => false,
                'message' => 'Slot already booked',
            ], 409);
        }

        $service = Service::where('status', true)
            ->findOrFail($request->service_id);

        $amount = $this->calculatePrice($service);

        $booking = Booking::create([
            'customer_id'    => $customer->id,
            'service_id'     => $service->id,
            'booking_date'   => $request->booking_date,
            'time_slot'      => $request->time_slot,
            'address'        => $request->address,
            'city'           => $request->city,
            'pincode'        => $request->pincode,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'amount'         => $amount,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
            'source'         => $request->source ?? 'whatsapp',
            'bot_session_id' => $request->bot_session_id,
        ]);

        return response()->json([
            'success'    => true,
            'booking_id' => $booking->id,
            'amount'     => $amount,
            'currency'   => 'INR',
            'status'     => 'pending',
        ]);
    }

    /**
     *  Available Slots
     */
    public function slots(Request $request)
    {
        $request->validate([
            'service_id'   => 'required|exists:services,id',
            'booking_date' => 'required|date',
        ]);

        // Future-proof: service can affect slots
        $service = Service::findOrFail($request->service_id);

        $allSlots = [
            '09:00 AM - 10:00 AM',
            '10:00 AM - 11:00 AM',
            '11:00 AM - 12:00 PM',
            '01:00 PM - 02:00 PM',
            '02:00 PM - 03:00 PM',
            '03:00 PM - 04:00 PM',
        ];

        $bookedSlots = Booking::where('booking_date', $request->booking_date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('time_slot')
            ->toArray();

        return response()->json([
            'success' => true,
            'slots'   => array_values(array_diff($allSlots, $bookedSlots)),
        ]);
    }

    /**
     *  Create Booking (WhatsApp / CRM)
     */
    public function store(Request $request)
    {
        $request->validate([
            'phone'        => 'required|string',
            'service_id'   => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'time_slot'    => 'required|string',
            'address'      => 'required|string',
            'source'       => 'nullable|string',
        ]);

        // Customer
        $customer = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'name'      => 'WhatsApp Customer',
                'password'  => bcrypt(Str::random(10)),
                'role'      => 'customer',
                'is_active' => true,
            ]
        );

        // Slot lock
        $slotTaken = Booking::where('booking_date', $request->booking_date)
            ->where('time_slot', $request->time_slot)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($slotTaken) {
            return response()->json([
                'success' => false,
                'message' => 'Slot already booked',
            ], 409);
        }

        // Service + price
        $service = Service::where('status', true)
            ->findOrFail($request->service_id);

        $amount = $this->calculatePrice($service);

        // Create booking
        $booking = Booking::create([
            'customer_id'    => $customer->id,
            'service_id'     => $service->id,
            'booking_date'   => $request->booking_date,
            'time_slot'      => $request->time_slot,
            'address'        => $request->address,
            'amount'         => $amount,
            'status'         => 'pending',
            'payment_status' => 'unpaid',
            'source'         => $request->source ?? 'whatsapp',
        ]);

        return response()->json([
            'success'    => true,
            'booking_id' => $booking->id,
            'amount'     => $amount,
            'currency'   => 'INR',
            'status'     => 'pending',
        ]);
    }

    /**
     *  Booking Details (CRM)
     */
    public function show($id)
    {
        $booking = Booking::with('service.category')->findOrFail($id);

        return response()->json([
            'id'             => $booking->id,
            'service'        => $booking->service->name,
            'category'       => $booking->service->category->name ?? null,
            'amount'         => $booking->amount,
            'booking_date'   => $booking->booking_date,
            'time_slot'      => $booking->time_slot,
            'status'         => $booking->status,
            'payment_status' => $booking->payment_status,
            'address'        => $booking->address,
        ]);
    }

    /**
     *  PRICE CALCULATOR (Central Logic)
     */
    private function calculatePrice(Service $service): float
    {
        $isFestival = false;

        if ($isFestival && $service->festival_price) {
            return (float) $service->festival_price;
        }

        return (float) $service->price;
    }

}
