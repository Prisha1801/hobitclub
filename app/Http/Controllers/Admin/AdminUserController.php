<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        $adminRoleId = \App\Models\Role::where('slug', 'super-admin')->value('id');
        return User::where('role_id', $adminRoleId)->latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'role' => 'admin'
        ]);

        return response()->json(['message' => 'Admin created']);
    }

    public function users()
    {
        return User::with('role')->get();
    }

    public function destroy(User $user)
    {
        // Allow only super-admin
        if (!auth()->user()->hasRole('super-admin')) {
            abort(403, 'Only super-admin can delete admin users');
        }
        $adminRoleId = \App\Models\Role::where('slug', 'super-admin')->value('id');
        // Prevent deleting super-admin itself
        if ($user->role_id === $adminRoleId) {
            abort(403, 'Super-admin cannot be deleted');
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully'
        ]);
    }
}
