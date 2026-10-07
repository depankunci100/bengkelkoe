<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'category', // ENGINE, BRAKE, ELECTRICAL, SUSPENSION, BODY_INTERIOR
        'item_name',
        'condition', // GOOD, WARNING, BAD, NEED_REPLACEMENT
        'notes',
        'photo_path',
    ];

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function getConditionBadgeAttribute(): array
    {
        return match ($this->condition) {
            'GOOD' => ['class' => 'bg-success', 'label' => 'Baik (Good)'],
            'WARNING' => ['class' => 'bg-warning text-dark', 'label' => 'Perhatian (Warning)'],
            'BAD' => ['class' => 'bg-danger', 'label' => 'Rusak (Bad)'],
            'NEED_REPLACEMENT' => ['class' => 'bg-danger', 'label' => 'Perlu Diganti (Replace)'],
            default => ['class' => 'bg-secondary', 'label' => $this->condition],
        };
    }
}
