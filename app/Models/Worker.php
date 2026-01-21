<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',

        // Profile
        'full_name',
        'phone',
        'service_category',
        'preferred_area',

        // KYC
        'kyc_status',
        'id_type',
        'id_number',
        'id_front_path',
        'id_back_path',

        // Skills & availability
        'skills',
        'available_days',
        'available_time',

        // Finance
        'wallet_balance',
    ];

    /**
     * Cast JSON fields automatically
     */
    protected $casts = [
        'skills' => 'array',
        'available_days' => 'array',
        'available_time' => 'array',
        'wallet_balance' => 'decimal:2',
    ];

    /**
     * Worker belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Worker has many bookings
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope: only KYC approved workers
     */
    public function scopeApproved($query)
    {
        return $query->where('kyc_status', 'approved');
    }

    /**
     * Scope: active workers only
     */
    public function scopeActive($query)
    {
        return $query->whereHas('user', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Helper: check if worker is eligible for assignment
     */
    public function isEligibleForAssignment(): bool
    {
        return $this->kyc_status === 'approved'
            && $this->user
            && $this->user->is_active
            && $this->user->is_phone_verified;
    }

    public function ratings()
    {
        return $this->hasMany(BookingRating::class);
    }

    public function averageRating()
    {
        return round($this->ratings()->avg('rating'), 1);
    }

    public function ratingsCount()
    {
        return $this->ratings()->count();
    }
}
