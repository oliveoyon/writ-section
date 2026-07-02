<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleLabelController extends Controller
{
    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:125'],
        ]);

        $role->forceFill($validated)->save();

        return response()->json([
            'success' => true,
            'message' => 'Role display name updated successfully.',
        ]);
    }
}
