<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResidentSurvey extends Model
{
    protected $fillable = [
        'surveyor_name',
        'building_name',
        'flat_number',
        'resident_name',
        'family_members',
        'maids_count',
        'work_types',
        'work_duration',
        'reliability_rating',
        'problems_faced',
        'preferred_time_slots',
        'monthly_payment_range',
        'maid_source',
        'app_openness',
        'convincing_factors',
        'extra_payment',
        'whatsapp_group_interest'
    ];

    protected $casts = [
        'work_types' => 'array',
        'problems_faced' => 'array',
        'preferred_time_slots' => 'array',
        'maid_source' => 'array',
        'convincing_factors' => 'array'
    ];
}
