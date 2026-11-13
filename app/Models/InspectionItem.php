<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id', 'category', 'title', 'technician_comment', 'urgency',
        'is_checked', 'estimated_cost', 'sort_order',
    ];

    public function isAdHoc(): bool
    {
        return $this->category === null;
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(InspectionMedia::class);
    }
}
