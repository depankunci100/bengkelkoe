<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_number',
        'barcode',
        'name',
        'brand',
        'category',
        'part_category_id',
        'cost_price',
        'selling_price',
        'stock',
        'min_stock',
        'unit',
        'location',
        'supplier',
        'supplier_id',
        'is_active',
    ];

    public static function findByBarcodeOrNumber(string $code): ?self
    {
        $code = trim($code);
        return static::where('barcode', $code)
            ->orWhere('part_number', $code)
            ->first();
    }

    public function getEffectiveBarcodeAttribute(): string
    {
        return $this->barcode ?: $this->part_number;
    }

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function isLowStock(): bool
    {
        return $this->stock > 0 && $this->stock <= $this->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function supplierRelation(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function partCategory(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class, 'part_category_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SupplierReturn::class);
    }
}

