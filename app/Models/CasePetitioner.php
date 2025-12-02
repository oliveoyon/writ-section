<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasePetitioner extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'name',
        'address',
        'phone',
        'email',
        'nid',
    ];

    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class, 'case_id');
    }
}
