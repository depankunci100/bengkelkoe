<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah foreign key supplier_sales_id ke stock_movements (pencatatan sales saat barang masuk / opname)
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('supplier_sales_id')->nullable()->after('supplier_id')->constrained('supplier_sales')->nullOnDelete();
        });

        // 2. Buat tabel supplier_returns (Faktur Pengembalian / Retur Barang Rusak ke Supplier)
        Schema::create('supplier_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique()->index();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_sales_id')->nullable()->constrained('supplier_sales')->nullOnDelete();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->string('batch_reference')->nullable()->index();
            $table->decimal('quantity', 8, 2);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('settlement_type')->default('REPLACEMENT'); // REPLACEMENT (Tukar Barang), REFUND (Potong Tagihan/Kembali Uang)
            $table->string('reason'); // Alasan kerusakan / cacat
            $table->text('notes')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, ACCEPTED, COMPLETED, REJECTED
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_returns');

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['supplier_sales_id']);
            $table->dropColumn('supplier_sales_id');
        });
    }
};
