<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Helpers\RoleIdGenerator;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role_id',
        'category_ids',
        'service_ids',
        'city_id',
        'zone_id',
        'area_id',
        'added_by',
        'public_id',
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
    
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        return $this->role?->permissions();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role
            && $this->role->permissions->contains('slug', $permission);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role && $this->role->slug === $slug;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('super_admin');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function addedUsers()
    {
        return $this->hasMany(User::class, 'added_by');
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->public_id) && $user->role) {
                $user->public_id = RoleIdGenerator::generate($user->role->slug);
            }
        });
    }
}
