<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'duration_minutes',
        'price',
        'festival_price',
        'subscription_id',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function subscription()
    {
        return $this->belongsTo(SubscriptionType::class, 'subscription_id');
    }

    public function extraTimeFees()
    {
        return $this->hasMany(ExtraTimeFee::class);
    }
    
}
