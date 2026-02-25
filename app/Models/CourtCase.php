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
        'initiated_by_user_id',
        'entry_source',
        'case_type',
        'subject',
        'description',
        'status',
        'temporary_barcode',
        'temporary_barcode_generated_at',
        'permanent_barcode',
        'permanent_barcode_generated_at',
        'section_verified_at',
        'section_verified_by',
        'final_case_number',
        'final_case_year',
        'current_section',
        'current_holder_user_id',
        'current_holder_at',
        'returned_at',
        'returned_by_user_id',
        'return_reason',
    ];

    protected $casts = [
        'temporary_barcode_generated_at' => 'datetime',
        'permanent_barcode_generated_at' => 'datetime',
        'section_verified_at' => 'datetime',
        'current_holder_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    // Relationship: CourtCase belongs to a Lawyer
    public function lawyer()
    {
        return $this->belongsTo(Lawyer::class);
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
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

    public function movements()
    {
        return $this->hasMany(FileMovement::class, 'case_id');
    }

    public function latestMovement()
    {
        return $this->hasOne(FileMovement::class, 'case_id')->latestOfMany();
    }

    public function currentHolder()
    {
        return $this->belongsTo(User::class, 'current_holder_user_id');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by_user_id');
    }

    // Relationship: verified by section admin user
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'section_verified_by');
    }
}
