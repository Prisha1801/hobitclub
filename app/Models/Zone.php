<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['city_id', 'name', 'status'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function areas()
    {
        return $this->hasMany(ServiceableArea::class);
    }
}
