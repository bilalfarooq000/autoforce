<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InventoryMovement extends Model
{
    use HasFactory, LogsActivity;

    public const TYPES = [
        'stock_in' => 'Stock In',
        'adjustment_in' => 'Adjustment (+)',
        'adjustment_out' => 'Adjustment (-)',
        'issued_to_job' => 'Issued to Job',
        'return_from_job' => 'Returned from Job',
    ];

    public const INCREASING = ['stock_in', 'adjustment_in', 'return_from_job'];
    public const DECREASING = ['adjustment_out', 'issued_to_job'];

    protected $fillable = [
        'branch_id', 'item_id', 'type', 'quantity', 'unit_cost', 'reference', 'inspection_id', 'performed_by',
    ];

    protected function casts(): array
    {
        return ['unit_cost' => 'decimal:2'];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function signedQuantity(): int
    {
        return in_array($this->type, self::DECREASING) ? -$this->quantity : $this->quantity;
    }
}
