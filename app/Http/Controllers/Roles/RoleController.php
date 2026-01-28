<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        return Role::latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'description' => 'nullable|string',
        ]);

        return Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description
        ]);
    }

    public function show(Role $role)
    {
        return $role->load('permissions');
    }

    public function update(Request $request, Role $role)
    {
        // Optional: protect system roles
        if ($role->is_system ?? false) {
            return response()->json([
                'message' => 'System roles cannot be updated'
            ], 403);
        }

        $request->validate([
            'name'        => 'sometimes|string|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
        ]);

        $role->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name), // regenerate slug
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Role updated successfully',
            'role'    => $role
        ]);
    }

    public function destroy(Role $role)
    {
        // Optional: protect system roles
        if ($role->is_system ?? false) {
            return response()->json([
                'message' => 'System roles cannot be deleted'
            ], 403);
        }

        // Prevent deleting role assigned to users
        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'Role is assigned to users and cannot be deleted'
            ], 409);
        }

        $role->permissions()->detach(); // clean pivot
        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully'
        ]);
    }
}
