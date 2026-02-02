<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CommissionCalculate;
use App\Models\Commission;

class CommissionService
{
    public static function calculate(Booking $booking)
    {
        $commissionConfig = Commission::where('service_id', $booking->service_id)
            ->where('status', 'active')
            ->first();

        if (!$commissionConfig) {
            return null;
        }

        $amount = $booking->amount;

        if ($commissionConfig->commission_type === 'fixed') {
            $companyCommission = $commissionConfig->value;
        } else {
            $companyCommission = ($amount * $commissionConfig->value) / 100;
        }

        $workerEarning = $amount - $companyCommission;

        return CommissionCalculate::create([
            'booking_id'          => $booking->id,
            'service_id'          => $booking->service_id,
            'worker_id'           => $booking->worker_id,
            'booking_amount'      => $amount,
            'company_commission'  => round($companyCommission, 2),
            'worker_earning'      => round($workerEarning, 2),
            'commission_type'     => $commissionConfig->commission_type,
            'commission_value'    => $commissionConfig->value,
        ]);
    }
}