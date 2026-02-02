<?php

namespace App\Http\Controllers\Survey;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaidSurvey;

class MaidSurveyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'supervisor_name' => 'nullable|string',

            'maid_name' => 'required|string',
            'age' => 'nullable|integer',
            'mobile_number' => 'nullable|string',
            'address' => 'nullable|string',

            'houses_count' => 'nullable|integer',
            'buildings_count' => 'nullable|integer',
            'family_members' => 'nullable|integer',

            'only_earning_member' => 'nullable|string',
            'has_children' => 'nullable|string',

            'work_types' => 'nullable|array',

            'daily_work_hours' => 'nullable|integer',
            'area_experience' => 'nullable|string',

            'charge_per_house' => 'nullable|string',
            'monthly_income' => 'nullable|integer',

            'paid_on_time' => 'nullable|string',
            'holidays' => 'nullable|string',
            'paid_leave' => 'nullable|string',

            'has_smartphone' => 'nullable|string',
            'uses_whatsapp' => 'nullable|string',
            'uses_online_payment' => 'nullable|string',

            'salary_online' => 'nullable|string',
            'interested_extra_work_app' => 'nullable|string',

            'work_priority' => 'nullable|array',

            'feels_safe' => 'nullable|string',
            'interested_in_benefits' => 'nullable|string'
        ]);

        $survey = MaidSurvey::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Survey submitted successfully',
            'data' => $survey
        ], 201);
    }

    // GET ALL
    public function index()
    {
        return MaidSurvey::latest()->paginate(20);
        return response()->json([
            'status' => true,
            'data' => MaidSurvey::latest()->get()
        ]);
    }

     public function show($id)
    {
        return response()->json([
            'status' => true,
            'data' => MaidSurvey::findOrFail($id)
        ]);
    }
}
