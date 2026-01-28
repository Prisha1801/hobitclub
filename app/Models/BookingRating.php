<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRating extends Model
{
    protected $fillable = [
        'booking_id',
        'worker_id',
        'customer_id',
        'rating',
        'description'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }
}
