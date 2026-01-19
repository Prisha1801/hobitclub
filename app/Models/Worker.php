<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_category',
        'wallet_balance',
        'kyc_status'
    ];

    // Worker belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
