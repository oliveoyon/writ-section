<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtDispatchBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_no',
        'court_id',
        'created_by_user_id',
        'type',
        'dispatched_at',
        'returned_at',
        'received_by_name',
        'received_by_designation',
        'received_by_phone',
        'handover_to_section',
        'notes',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(CourtDispatchBatchItem::class, 'batch_id');
    }
}
