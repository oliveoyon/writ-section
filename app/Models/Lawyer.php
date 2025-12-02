<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lawyer extends Model
{
    use HasFactory;

    // Fillable fields
    protected $fillable = [
        'user_id',
        'bar_council_id',
        'full_name',
        'phone',
        'picture',
        'barDateOfJoining',
        'barDateOfEnrollment',
        'barCourtType',
        'status',
    ];


    /**
     * Relation to User
     * Each lawyer belongs to one user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cases()
    {
        return $this->hasMany(CourtCase::class, 'lawyer_id');
    }
}
