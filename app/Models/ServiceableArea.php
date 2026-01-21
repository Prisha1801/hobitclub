<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceableArea extends Model
{
    protected $fillable = [
        'zone_id',
        'name',
        'pincode',
        'latitude',
        'longitude',
        'status'
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
