<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'wo_number',
        'customer_id',
        'vehicle_id',
        'technician_id',
        'supplier_id',
        'supplier_sales_id',
        'created_by',
        'status',
        'complaint',
        'odometer_in',
        'technician_notes',
        'qc_notes',
        'total_services',
        'total_parts',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'approval_token',
        'approval_status',
        'approval_sent_at',
        'approval_expires_at',
        'approved_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_services' => 'decimal:2',
            'total_parts' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'approval_sent_at' => 'datetime',
            'approval_expires_at' => 'datetime',
            'approved_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public static function generateWoNumber(): string
    {
        $year = date('Y');
        $latest = static::whereYear('created_at', $year)->latest('id')->first();
        $nextNumber = $latest ? ((int) substr($latest->wo_number, -4) + 1) : 1;
        return sprintf("WO-%s-%04d", $year, $nextNumber);
    }

    public static function generateApprovalToken(): string
    {
        return Str::random(40);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function supplierSales(): BelongsTo
    {
        return $this->belongsTo(SupplierSales::class, 'supplier_sales_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WorkOrderItem::class);
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(Inspection::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(WoTimeline::class)->orderBy('created_at', 'asc');
    }

    public function whatsappLogs(): HasMany
    {
        return $this->hasMany(WhatsappLog::class)->latest('created_at');
    }

    public function recalculateTotals(): void
    {
        $services = $this->items()->where('type', 'SERVICE')->sum('subtotal');
        $parts = $this->items()->where('type', 'PART')->sum('subtotal');
        $subtotal = $services + $parts;
        $tax = round(($subtotal - $this->discount) * 0.11, 2); // PPN 11%
        $grandTotal = max(0, ($subtotal - $this->discount) + $tax);

        $this->update([
            'total_services' => $services,
            'total_parts' => $parts,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'grand_total' => $grandTotal,
        ]);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'DRAFT' => ['class' => 'bg-secondary', 'label' => 'Draft', 'icon' => 'bi-file-earmark'],
            'INSPECTION' => ['class' => 'bg-info text-dark', 'label' => 'Inspeksi', 'icon' => 'bi-search'],
            'WAITING_APPROVAL' => ['class' => 'bg-warning text-dark', 'label' => 'Menunggu Approval', 'icon' => 'bi-clock-history'],
            'APPROVED' => ['class' => 'bg-primary', 'label' => 'Disetujui', 'icon' => 'bi-check2-circle'],
            'IN_PROGRESS' => ['class' => 'bg-primary', 'label' => 'Dikerjakan', 'icon' => 'bi-wrench-adjustable'],
            'PAUSED' => ['class' => 'bg-secondary', 'label' => 'Ditunda', 'icon' => 'bi-pause-circle'],
            'QC' => ['class' => 'bg-info text-dark', 'label' => 'Quality Control', 'icon' => 'bi-clipboard-check'],
            'READY_FOR_PICKUP' => ['class' => 'bg-success', 'label' => 'Siap Diambil', 'icon' => 'bi-check-all'],
            'COMPLETED' => ['class' => 'bg-success', 'label' => 'Selesai', 'icon' => 'bi-flag-fill'],
            'REJECTED' => ['class' => 'bg-danger', 'label' => 'Ditolak', 'icon' => 'bi-x-circle'],
            'CANCELLED' => ['class' => 'bg-danger', 'label' => 'Dibatalkan', 'icon' => 'bi-slash-circle'],
            default => ['class' => 'bg-secondary', 'label' => $this->status, 'icon' => 'bi-circle'],
        };
    }
}
