<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vehicle extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'customer_id', 'branch_id', 'registration_number', 'make', 'model',
        'year', 'chassis_number', 'engine_number', 'color', 'photo_path',
        'vehicle_type', 'fuel_type', 'transmission', 'mileage', 'purchase_date',
        'insurance_expiry', 'next_service_due_date', 'next_service_due_mileage', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'insurance_expiry' => 'date',
            'next_service_due_date' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function isServiceDueSoon(): bool
    {
        if ($this->next_service_due_date && $this->next_service_due_date->isBefore(now()->addDays(14))) {
            return true;
        }

        return $this->next_service_due_mileage && $this->mileage >= ($this->next_service_due_mileage - 500);
    }

    public function insuranceExpiringSoon(): bool
    {
        return $this->insurance_expiry && $this->insurance_expiry->isBefore(now()->addDays(30));
    }

    /**
     * One-click, chronological view of everything that has happened to this vehicle:
     * check-ins, diagnosis milestones, quotation decisions, invoices and payments.
     * Callers should eager-load inspections.quotation and invoices.payments first.
     */
    public function timeline(bool $forCustomer = false): \Illuminate\Support\Collection
    {
        $events = collect([
            ['at' => $this->created_at, 'label' => 'Vehicle registered at AutoForce', 'type' => 'vehicle', 'url' => null],
        ]);

        foreach ($this->inspections as $inspection) {
            $jobUrl = $forCustomer ? route('portal.inspection', $inspection->access_token) : route('staff.inspections.show', $inspection);

            $events->push(['at' => $inspection->created_at, 'label' => "Checked in — {$inspection->reason}", 'type' => 'checkin', 'url' => $jobUrl]);

            if ($inspection->inspection_started_at) {
                $events->push(['at' => $inspection->inspection_started_at, 'label' => 'Inspection started'.($inspection->technician && ! $forCustomer ? " by {$inspection->technician->name}" : ''), 'type' => 'progress', 'url' => $jobUrl]);
            }
            if ($inspection->diagnosis_completed_at) {
                $events->push(['at' => $inspection->diagnosis_completed_at, 'label' => 'Diagnosis completed', 'type' => 'progress', 'url' => $jobUrl]);
            }
            if ($inspection->quotation) {
                $events->push(['at' => $inspection->quotation->created_at, 'label' => 'Quotation sent — PKR '.number_format($inspection->quotation->total_amount), 'type' => 'quotation', 'url' => $jobUrl]);
                if ($inspection->quotation->decided_at) {
                    $events->push(['at' => $inspection->quotation->decided_at, 'label' => 'Customer '.$inspection->quotation->status.' the quotation', 'type' => $inspection->quotation->status === 'approved' ? 'approved' : 'declined', 'url' => $jobUrl]);
                }
            }
            if ($inspection->ready_at) {
                $events->push(['at' => $inspection->ready_at, 'label' => 'Vehicle marked ready for collection', 'type' => 'ready', 'url' => $jobUrl]);
            }
            if ($inspection->collected_at) {
                $events->push(['at' => $inspection->collected_at, 'label' => 'Vehicle collected by customer', 'type' => 'collected', 'url' => $jobUrl]);
            }
        }

        foreach ($this->invoices as $invoice) {
            $invoiceUrl = $forCustomer ? null : route('staff.invoices.show', $invoice);

            $events->push(['at' => $invoice->issued_at, 'label' => "Invoice {$invoice->invoice_number} issued — PKR ".number_format($invoice->total), 'type' => 'invoice', 'url' => $invoiceUrl]);
            foreach ($invoice->payments as $payment) {
                $events->push(['at' => $payment->paid_at, 'label' => 'Payment received — PKR '.number_format($payment->amount).' ('.str($payment->method)->headline().')', 'type' => 'payment', 'url' => $invoiceUrl]);
            }
        }

        return $events->sortByDesc('at')->values();
    }
}
