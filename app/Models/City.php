<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
     protected $fillable = ['name', 'status'];

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }
}
