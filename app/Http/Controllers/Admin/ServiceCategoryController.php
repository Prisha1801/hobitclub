<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        return ServiceCategory::withCount('services')->get();
    }

    public function store(Request $request)
    {
        return ServiceCategory::create($request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|unique:service_categories,slug',
            'icon'        => 'nullable|string',
            'description' => 'nullable|string',
            'status'      => 'required|boolean'
        ]));
    }

    public function show(ServiceCategory $serviceCategory)
    {
        return $serviceCategory->load('services');
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $serviceCategory->update($request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|unique:service_categories,slug,' . $serviceCategory->id,
            'icon'        => 'nullable|string',
            'description' => 'nullable|string',
            'status'      => 'required|boolean'
        ]));

        return response()->json(['message' => 'Category updated']);
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        $serviceCategory->delete();
        return response()->json(['message' => 'Category deleted']);
    }
}
