<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaidSurvey extends Model
{
    protected $fillable = [
        'supervisor_name',
        'maid_name',
        'age',
        'mobile_number',
        'address',
        'houses_count',
        'buildings_count',
        'family_members',
        'only_earning_member',
        'has_children',
        'work_types',
        'daily_work_hours',
        'area_experience',
        'charge_per_house',
        'monthly_income',
        'paid_on_time',
        'holidays',
        'paid_leave',
        'has_smartphone',
        'uses_whatsapp',
        'uses_online_payment',
        'salary_online',
        'interested_extra_work_app',
        'work_priority',
        'feels_safe',
        'interested_in_benefits'
    ];

    protected $casts = [
        'work_types' => 'array',
        'work_priority' => 'array'
    ];
}
