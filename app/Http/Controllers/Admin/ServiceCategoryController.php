<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;

class ServiceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ServiceCategory::withCount('services')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceCategoryRequest $request)
    {
        return ServiceCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'description' => $request->description,
            'status' => $request->status ?? true,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = ServiceCategory::findOrFail($id);
        return $category;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreServiceCategoryRequest $request, $id)
    {
        $category = ServiceCategory::findOrFail($id);
        $category->update($request->validated());
        return $category;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = ServiceCategory::withCount('services')->findOrFail($id);

        if ($category->services_count > 0) {
            return response()->json([
                'message' => 'Cannot delete category with services'
            ], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
