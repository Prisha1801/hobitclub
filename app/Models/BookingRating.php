<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRating extends Model
{
    protected $fillable = [
        'booking_id',
        'worker_id',
        'user_id',
        'rating',
        'description'
    ];

    public function booking()
    {
        return $this->belongsTo(Order::class, 'booking_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
