<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileTransferBatch extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 0;

    public const STATUS_PARTIALLY_RECEIVED = 1;

    public const STATUS_COMPLETED = 2;

    public const STATUS_CANCELLED = 3;

    protected $fillable = [
        'batch_no',
        'sender_user_id',
        'recipient_user_id',
        'sender_department_id',
        'recipient_department_id',
        'sender_name',
        'sender_employee_id',
        'sender_section',
        'recipient_name',
        'recipient_employee_id',
        'recipient_section',
        'status',
        'sent_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by_user_id',
        'cancellation_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PARTIALLY_RECEIVED => 'Partially Received',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function senderDepartment()
    {
        return $this->belongsTo(Department::class, 'sender_department_id');
    }

    public function recipientDepartment()
    {
        return $this->belongsTo(Department::class, 'recipient_department_id');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(FileTransferItem::class, 'batch_id');
    }

    public function pendingItems()
    {
        return $this->hasMany(FileTransferItem::class, 'batch_id')
            ->where('status', FileTransferItem::STATUS_PENDING);
    }
}
