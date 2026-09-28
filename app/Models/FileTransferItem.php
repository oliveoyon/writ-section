<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileTransferItem extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 0;

    public const STATUS_RECEIVED = 1;

    public const STATUS_CANCELLED = 2;

    protected $fillable = [
        'batch_id',
        'case_id',
        'active_case_id',
        'status',
        'sent_at',
        'received_at',
        'received_by_user_id',
        'file_movement_id',
        'cancelled_at',
        'cancelled_by_user_id',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function batch()
    {
        return $this->belongsTo(FileTransferBatch::class, 'batch_id');
    }

    public function courtCase()
    {
        return $this->belongsTo(CourtCase::class, 'case_id');
    }

    public function activeCase()
    {
        return $this->belongsTo(CourtCase::class, 'active_case_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function movement()
    {
        return $this->belongsTo(FileMovement::class, 'file_movement_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function receiptDelayInSeconds(): ?int
    {
        if (!$this->sent_at || !$this->received_at) {
            return null;
        }

        return $this->sent_at->diffInSeconds($this->received_at);
    }
}
