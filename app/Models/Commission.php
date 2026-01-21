<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'service_id',
        'commission_type',
        'value',
        'status'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function calculate(float $amount): float
    {
        return $this->commission_type === 'percentage'
            ? round(($amount * $this->value) / 100, 2)
            : $this->value;
    }
}
