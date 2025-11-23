<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class PermissionGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relationship: a group has many permissions
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'group_id');
    }
}
