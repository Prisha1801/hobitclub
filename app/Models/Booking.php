<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'service_id',
        'worker_id',
        'booking_date',
        'time_slot',
        'address',
        'city',
        'pincode',
        'latitude',
        'longitude',
        'amount',
        'status',
        'payment_status',
        'source',
        'bot_session_id',
        'approved_by',
        'approved_at',
        'payment_ref',
        'paid_at',
        'assigned_by',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
