<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'type', // SERVICE, PART
        'service_id',
        'part_id',
        'item_name',
        'quantity',
        'unit_price',
        'subtotal',
        'approval_status', // PENDING, APPROVED, REJECTED
        'is_additional',
        'is_verified',
        'verified_quantity',
        'verified_at',
        'verified_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'is_additional' => 'boolean',
            'is_verified' => 'boolean',
            'verified_quantity' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function isFullyVerified(): bool
    {
        if ($this->type !== 'PART') {
            return true;
        }
        return $this->is_verified || (float) $this->verified_quantity >= (float) $this->quantity;
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
