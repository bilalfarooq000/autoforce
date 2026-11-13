<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inspection extends Model
{
    use HasFactory, LogsActivity;

    public const STATUSES = [
        'checked_in' => 'Checked In',
        'inspecting' => 'Inspecting',
        'awaiting_approval' => 'Awaiting Approval',
        'in_repair' => 'In Repair',
        'ready_for_collection' => 'Ready for Collection',
        'completed' => 'Completed',
    ];

    protected $fillable = [
        'vehicle_id', 'branch_id', 'technician_id', 'created_by', 'reason', 'mileage_at_visit',
        'status', 'access_token', 'summary',
        'inspection_started_at', 'diagnosis_completed_at', 'ready_at', 'collected_at',
    ];

    protected function casts(): array
    {
        return [
            'inspection_started_at' => 'datetime',
            'diagnosis_completed_at' => 'datetime',
            'ready_at' => 'datetime',
            'collected_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::creating(function (Inspection $inspection) {
            $inspection->access_token ??= (string) Str::uuid();
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InspectionItem::class);
    }

    public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function diagramMarks(): HasMany
    {
        return $this->hasMany(InspectionDiagramMark::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(JobComment::class)->latest();
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function portalUrl(): string
    {
        return route('portal.inspection', $this->access_token);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function turnaroundHours(): ?float
    {
        $end = $this->collected_at ?? $this->ready_at;

        if (! $end || $end->lessThan($this->created_at)) {
            return null;
        }

        return round($this->created_at->diffInMinutes($end) / 60, 1);
    }
}
