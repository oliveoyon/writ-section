<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public const CANONICAL_NAMES = [
        'Filing Section',
        'Affidavit Section',
        'Requisite',
        'Put-Up',
        'Typing',
        'Compare',
        'Superintendent',
        'Ready Table',
        'Record Room',
        'Registrar',
    ];
}
