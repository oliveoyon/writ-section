<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = ['name', 'guard_name', 'group_id'];

    // Add relation to PermissionGroup
    public function group()
    {
        return $this->belongsTo(\App\Models\PermissionGroup::class, 'group_id');
    }
}
