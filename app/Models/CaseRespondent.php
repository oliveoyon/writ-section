<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseRespondent extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'name',
        'designation',
        'organization',
        'address',
    ];

    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class, 'case_id');
    }
}
