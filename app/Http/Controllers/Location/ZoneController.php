<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zone;
use Illuminate\Foundation\Http\FormRequest;

class ZoneController extends Controller
{
    public function index()
    {
        return Zone::with('city')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required'
        ]);

        return Zone::create($request->only('city_id','name','status'));
    }

    public function show($id)
    {
        $Zone = Zone::findOrFail($id);
        return $Zone;
    }

    public function update(Request $request, $id)
    {
        $zone_data = $request->validate([
            'city_id' => 'sometimes|exists:cities,id',
            'name'    => 'sometimes|string'
        ]);

        $zone = Zone::findOrFail($id);
        $zone->update($zone_data);
        return $zone;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Zone::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
