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
        // 1. Tabel Sales Representatif dari Supplier (1 Supplier punya banyak Sales)
        Schema::create('supplier_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('area')->nullable(); // misal: Surabaya Barat, Fast Moving, dll.
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Hubungkan Work Order ke Supplier dan Sales terkait
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('technician_id')->constrained('suppliers')->nullOnDelete();
            $table->foreignId('supplier_sales_id')->nullable()->after('supplier_id')->constrained('supplier_sales')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_sales_id');
            $table->dropConstrainedForeignId('supplier_id');
        });

        Schema::dropIfExists('supplier_sales');
    }
};
