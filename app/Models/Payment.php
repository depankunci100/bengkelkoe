<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_number',
        'amount',
        'payment_method', // CASH, BANK_TRANSFER, QRIS, DEBIT_CREDIT
        'reference_number',
        'cashier_id',
        'notes',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public static function generatePaymentNumber(): string
    {
        $year = date('Y');
        $latest = static::whereYear('created_at', $year)->latest('id')->first();
        $nextNumber = $latest ? ((int) substr($latest->payment_number, -4) + 1) : 1;
        return sprintf("PAY-%s-%04d", $year, $nextNumber);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function getMethodBadgeAttribute(): array
    {
        return match ($this->payment_method) {
            'CASH' => ['class' => 'bg-success', 'label' => 'Tunai (Cash)', 'icon' => 'bi-cash-coin'],
            'BANK_TRANSFER' => ['class' => 'bg-primary', 'label' => 'Transfer Bank', 'icon' => 'bi-bank'],
            'QRIS' => ['class' => 'bg-danger', 'label' => 'QRIS', 'icon' => 'bi-qr-code'],
            'DEBIT_CREDIT' => ['class' => 'bg-info text-dark', 'label' => 'Kartu Debit/Kredit', 'icon' => 'bi-credit-card'],
            default => ['class' => 'bg-secondary', 'label' => $this->payment_method, 'icon' => 'bi-wallet2'],
        };
    }
}
