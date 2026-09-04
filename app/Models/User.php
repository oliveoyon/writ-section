<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'user_type',
        'is_active',
        'login_id',
        'employee_id',
        'department', // stores the FK (departments.id)
        'face_descriptor',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'face_descriptor' => 'array',
        ];
    }

    public function lawyer()
    {
        return $this->hasOne(Lawyer::class);
    }

    public function departmentRelation()
    {
        // The column in users table is 'department', but it references departments.id
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function receivedMovements()
    {
        return $this->hasMany(FileMovement::class, 'received_by_user_id');
    }

    public function courtDispatchBatches()
    {
        return $this->hasMany(CourtDispatchBatch::class, 'created_by_user_id');
    }
}
