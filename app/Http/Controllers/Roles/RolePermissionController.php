<?php

namespace App\Http\Controllers\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;

class RolePermissionController extends Controller
{
    public function edit(Role $role)
    {
        return [
            'role' => $role,
            'permissions' => Permission::all(),
            'assigned' => $role->permissions->pluck('id')
        ];
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permission_ids' => 'array'
        ]);

        $role->permissions()->sync($request->permission_ids);

        return response()->json(['message' => 'Permissions updated']);
    }
}
