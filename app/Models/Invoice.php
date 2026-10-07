<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'work_order_id',
        'customer_id',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'amount_paid',
        'balance_due',
        'payment_status', // UNPAID, PARTIAL, PAID
        'cashier_id',
        'notes',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $latest = static::whereYear('created_at', $year)->latest('id')->first();
        $nextNumber = $latest ? ((int) substr($latest->invoice_number, -4) + 1) : 1;
        return sprintf("INV-%s-%04d", $year, $nextNumber);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculatePaymentStatus(): void
    {
        $paid = $this->payments()->sum('amount');
        $balance = max(0, $this->grand_total - $paid);

        $status = 'UNPAID';
        if ($balance <= 0 && $paid > 0) {
            $status = 'PAID';
        } elseif ($paid > 0) {
            $status = 'PARTIAL';
        }

        $this->update([
            'amount_paid' => $paid,
            'balance_due' => $balance,
            'payment_status' => $status,
        ]);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->payment_status) {
            'PAID' => ['class' => 'bg-success', 'label' => 'Lunas'],
            'PARTIAL' => ['class' => 'bg-warning text-dark', 'label' => 'Sebagian (DP)'],
            'UNPAID' => ['class' => 'bg-danger', 'label' => 'Belum Dibayar'],
            default => ['class' => 'bg-secondary', 'label' => $this->payment_status],
        };
    }
}
