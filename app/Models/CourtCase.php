<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtCase extends Model
{
    use HasFactory;

    protected $table = 'cases'; // important because model name is different

    protected $fillable = [
        'lawyer_id',
        'case_type',
        'subject',
        'description',
        'status',
        'temporary_barcode',
        'temporary_barcode_generated_at',
        'section_verified_at',
        'section_verified_by',
        'final_case_number',
        'final_case_year',
    ];

    // Relationship: CourtCase belongs to a Lawyer
    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class);
    }

    // Relationship: multiple petitioners
    public function petitioners()
    {
        return $this->hasMany(CasePetitioner::class, 'case_id');
    }

    // Relationship: multiple respondents
    public function respondents()
    {
        return $this->hasMany(CaseRespondent::class, 'case_id');
    }

    // Relationship: multiple file attachments
    public function files()
    {
        return $this->hasMany(CaseFile::class, 'case_id');
    }

    // Relationship: verified by section admin user
    public function verifiedBy()
    {
        return $this->belongsTo(AdminUser::class, 'section_verified_by');
    }
}
