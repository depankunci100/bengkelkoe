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
        // 1. Master Suppliers (Pemasok Sparepart)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Master Kategori Sparepart (Mesin, Kaki-kaki, Transmisi, dll.)
        Schema::create('part_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->default('bi-gear');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Tambahkan foreign key ke tabel parts
        Schema::table('parts', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('supplier')->constrained('suppliers')->nullOnDelete();
            $table->foreignId('part_category_id')->nullable()->after('category')->constrained('part_categories')->nullOnDelete();
        });

        // 4. Tambahkan foreign key supplier ke tabel stock_movements
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('supplier')->constrained('suppliers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('part_category_id');
            $table->dropConstrainedForeignId('supplier_id');
        });

        Schema::dropIfExists('part_categories');
        Schema::dropIfExists('suppliers');
    }
};
