<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionType extends Model
{
    protected $table = 'subscription_type';

    protected $fillable = ['name'];

    public function services()
    {
        return $this->hasMany(Service::class, 'subscription_id');
    }
}
