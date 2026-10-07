<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_number',
        'supplier_id',
        'supplier_sales_id',
        'part_id',
        'stock_movement_id',
        'batch_reference',
        'quantity',
        'cost_price',
        'total_amount',
        'settlement_type',
        'reason',
        'notes',
        'status',
        'user_id',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'resolved_at' => 'datetime',
        ];
    }

    public static function generateReturnNumber(): string
    {
        $year = date('Y');
        $latest = static::whereYear('created_at', $year)->latest('id')->first();
        $nextNumber = $latest ? ((int) substr($latest->return_number, -4) + 1) : 1;
        return sprintf("RET-%s-%04d", $year, $nextNumber);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function supplierSales(): BelongsTo
    {
        return $this->belongsTo(SupplierSales::class, 'supplier_sales_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'PENDING' => ['class' => 'bg-warning text-dark', 'label' => 'Diajukan ke Sales', 'icon' => 'bi-clock-history'],
            'ACCEPTED' => ['class' => 'bg-info text-dark', 'label' => 'Dikonfirmasi Sales', 'icon' => 'bi-check2-circle'],
            'COMPLETED' => ['class' => 'bg-success', 'label' => 'Selesai (Tukar/Refund)', 'icon' => 'bi-patch-check-fill'],
            'REJECTED' => ['class' => 'bg-danger', 'label' => 'Ditolak Supplier', 'icon' => 'bi-x-circle'],
            default => ['class' => 'bg-secondary', 'label' => $this->status, 'icon' => 'bi-circle'],
        };
    }

    public function getSettlementLabelAttribute(): string
    {
        return match ($this->settlement_type) {
            'REPLACEMENT' => 'Tukar Barang Baru (Replacement)',
            'REFUND' => 'Potong Tagihan / Pengembalian Dana',
            default => $this->settlement_type,
        };
    }
}
