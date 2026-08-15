<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseRespondent extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'name_or_organization',
        'represented_by',
        'designation',
        'address',
    ];

    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class, 'case_id');
    }
}
