<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ResidentSurvey;

class ResidentSurveyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'surveyor_name' => 'nullable|string',

            'building_name' => 'nullable|string',
            'flat_number' => 'nullable|string',
            'resident_name' => 'nullable|string',
            'family_members' => 'nullable|integer',

            'maids_count' => 'nullable|integer',
            'work_types' => 'nullable|array',
            'work_duration' => 'nullable|string',
            'reliability_rating' => 'nullable|integer|min:1|max:5',

            'problems_faced' => 'nullable|array',
            'preferred_time_slots' => 'nullable|array',

            'monthly_payment_range' => 'nullable|string',
            'maid_source' => 'nullable|array',

            'app_openness' => 'nullable|string',
            'convincing_factors' => 'nullable|array',
            'extra_payment' => 'nullable|string',
            'whatsapp_group_interest' => 'nullable|string'
        ]);

        $survey = ResidentSurvey::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Survey submitted successfully',
            'data' => $survey
        ], 201);
    }
}
