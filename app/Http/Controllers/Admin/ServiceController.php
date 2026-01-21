<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index()
    {
        return Service::with(['category','subscription'])->get();
    }

    public function store(Request $request)
    {
        return Service::create($request->validate([
            'category_id'       => 'required|exists:service_categories,id',
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'duration_minutes'  => 'nullable|integer',
            'price'             => 'required|numeric',
            'festival_price'    => 'nullable|numeric',
            'subscription_id'   => 'required|exists:subscription_type,id',
            'status'            => 'boolean'
        ]));
    }

    public function show(Service $service)
    {
        return $service->load(['category','subscription']);
    }

    public function update(Request $request, Service $service)
    {
        $service->update($request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'duration_minutes'  => 'nullable|integer',
            'price'             => 'required|numeric',
            'festival_price'    => 'nullable|numeric',
            'subscription_id'   => 'required|exists:subscription_type,id',
            'status'            => 'boolean'
        ]));

        return response()->json(['message' => 'Service updated']);
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Service deleted']);
    }
}
