<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_id',
        'court_id',
        'court_dispatch_batch_id',
        'barcode_scanned',
        'from_section',
        'to_section',
        'movement_type',
        'received_by_user_id',
        'received_at',
        'notes',
        'is_override',
        'override_reason',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'is_override' => 'boolean',
    ];

    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class, 'case_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function court()
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    public function dispatchBatch()
    {
        return $this->belongsTo(CourtDispatchBatch::class, 'court_dispatch_batch_id');
    }
}
