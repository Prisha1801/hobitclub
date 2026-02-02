<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionCalculate extends Model
{
    protected $table = 'commissions';

    protected $fillable = [
        'booking_id',
        'service_id',
        'worker_id',
        'booking_amount',
        'company_commission',
        'worker_earning',
        'commission_type',
        'commission_value',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
