<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceableArea;
use Illuminate\Foundation\Http\FormRequest;

class ServiceableAreaController extends Controller
{
    public function index()
    {
        return ServiceableArea::with('zone.city')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'zone_id' => 'required|exists:zones,id',
            'name' => 'required',
        ]);

        return ServiceableArea::create($request->all());
    }

    public function show($id)
    {
        $serviceableArea = ServiceableArea::findOrFail($id);
        return $serviceableArea;
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'zone_id' => 'sometimes|exists:zones,id',
            'name' => 'sometimes'
        ]);

        $serviceableArea = ServiceableArea::findOrFail($id);
        $serviceableArea->update($data);
        return $serviceableArea;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        ServiceableArea::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
