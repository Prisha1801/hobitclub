<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;

class CityController extends Controller
{
    public function index()
    {
        return City::withCount('zones')->get();
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:cities,name']);
        return City::create($request->only('name','status'));
    }

    public function show($id)
    {
        $city = City::findOrFail($id);
        return $city;
    }

    public function update(Request $request, $id)
    {
        $city_data = $request->validate([
            'name' => 'sometimes'
        ]);

        $city = City::findOrFail($id);
        $city->update($city_data);
        return $city;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        City::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
