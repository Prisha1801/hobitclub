<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraTimeFee extends Model
{
    protected $fillable = [
        'service_id',
        'minutes',
        'fee',
        'festival_fee',
        'is_active'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getFinalFeeAttribute()
    {
        return $this->festival_fee ?? $this->fee;
    }
}
