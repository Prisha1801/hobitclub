<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'worker_id',
        'service_id',
        'booking_date',
        'time_slot',
        'address',
        'status'
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function rating()
    {
        return $this->hasOne(BookingRating::class, 'booking_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
