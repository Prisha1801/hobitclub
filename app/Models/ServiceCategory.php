<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'status'
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
