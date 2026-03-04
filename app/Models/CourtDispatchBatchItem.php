<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtDispatchBatchItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'case_id',
        'barcode_scanned',
        'from_section',
        'to_section',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(CourtDispatchBatch::class, 'batch_id');
    }

    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class, 'case_id');
    }
}
