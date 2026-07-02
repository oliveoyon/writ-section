<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'display_name'];

    public function getLabelAttribute(): string
    {
        return $this->display_name ?: $this->name;
    }

    public const CANONICAL_NAMES = [
        'Assistant Registrar Office',
        'Office Assistant',
        'Filing Section',
        'Affidavit Section',
        'Requisite Section',
        'Put-Up Section',
        'Typing Section',
        'Compare Section',
        'Superintendent',
        'Ready Table',
        'Record Room',
        'Others',
        'Court Operator',
        'Registrar',
    ];
}
