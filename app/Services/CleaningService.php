<?php

namespace App\Services;

use App\Models\ServicePricing;
use App\Models\ExtraTimeFee;

class CleaningService
{
    /**
     * Calculate final price for a service
     */
    public function calculatePrice(
        int $servicePricingId,
        int $extraMinutes = 0,
        bool $isFestival = false
    ): array {
        $pricing = ServicePricing::with('service')->findOrFail($servicePricingId);

        // Base price
        $basePrice = $isFestival && $pricing->festival_price
            ? $pricing->festival_price
            : $pricing->price;

        // Extra time fee
        $extraFee = 0;

        if ($extraMinutes > 0) {
            $rule = ExtraTimeFee::where('service_id', $pricing->service_id)
                ->where('minutes', '<=', $extraMinutes)
                ->where('is_active', true)
                ->orderBy('minutes', 'desc')
                ->first();

            if ($rule) {
                $extraFee = $isFestival && $rule->festival_fee
                    ? $rule->festival_fee
                    : $rule->fee;
            }
        }

        return [
            'base_price'   => $basePrice,
            'extra_fee'    => $extraFee,
            'total_amount' => $basePrice + $extraFee,
        ];
    }
}
