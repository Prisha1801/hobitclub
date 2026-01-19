<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function index()
    {
        return User::where('role', 'worker')
            ->with('worker')
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'phone' => 'required|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'role' => 'worker'
        ]);

        $user->worker()->create([
            'service_category' => $request->service_category
        ]);

        return response()->json(['message' => 'Worker created']);
    }

    public function updateStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return response()->json(['message' => 'Status updated']);
    }
}

