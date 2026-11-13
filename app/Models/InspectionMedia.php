<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionMedia extends Model
{
    use HasFactory;

    protected $fillable = ['inspection_item_id', 'path', 'type', 'stage', 'caption'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InspectionItem::class, 'inspection_item_id');
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }
}
