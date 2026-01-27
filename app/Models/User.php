<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'category_ids',
        'service_ids',
        'city_id',
        'zone_id',
        'area_id',
        'is_active',
        'is_assigned'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_assigned' => 'boolean',
        'category_ids' => 'array',
        'service_ids'  => 'array',
    ];

    // WORKER RELATION
    public function worker()
    {
        return $this->hasOne(Worker::class);
    }

    // CUSTOMER RELATION
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
    
    public function area()
    {
        return $this->belongsTo(ServiceableArea::class, 'area_id');
    }

    public function worker_availablillity()
    {
        return $this->hasMany(WorkerAvailability::class, 'worker_id');
    }
    
}
