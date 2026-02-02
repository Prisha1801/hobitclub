<?php

namespace App\Http\Controllers\Time;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GetTimeController extends Controller
{
    public function getAvailableSlots()
    {
        $now = Carbon::now(config('app.timezone'));
        
         $slots = [
            [
                'label' => '9 AM - 12 PM',
                'start' => '09:00',
                'end'   => '12:00',
            ],
            [
                'label' => '12 PM - 3 PM',
                'start' => '12:00',
                'end'   => '15:00',
            ],
            [
                'label' => '3 PM - 5 PM',
                'start' => '15:00',
                'end'   => '17:00',
            ],
        ];

        $availableSlots = [];

        foreach ($slots as $slot) {

            $slotEnd = Carbon::today(config('app.timezone'))
                ->setTimeFromTimeString($slot['end']);

            // ✅ Show slot if current time is BEFORE end time
            if ($now->lt($slotEnd)) {
                $availableSlots[] = $slot['label'];
            }
        }

        return response()->json($availableSlots);
    }
}
