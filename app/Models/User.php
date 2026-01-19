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
        'is_active'
    ];

    protected $hidden = [
        'password',
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
}
