<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return User::where('role', 'customer')
            ->with('customer')
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users'
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => bcrypt('123456'),
            'role' => 'customer'
        ]);

        $user->customer()->create([
            'address' => $request->address
        ]);

        return response()->json(['message' => 'Customer created']);
    }
}

