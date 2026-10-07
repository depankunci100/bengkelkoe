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
        // 1. Tambah barcode pada tabel parts
        Schema::table('parts', function (Blueprint $table) {
            if (!Schema::hasColumn('parts', 'barcode')) {
                $table->string('barcode', 100)->nullable()->after('part_number')->index();
            }
        });

        // 2. Tambah kolom verifikasi barang keluar pada work_order_items
        Schema::table('work_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('work_order_items', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('is_additional');
            }
            if (!Schema::hasColumn('work_order_items', 'verified_quantity')) {
                $table->decimal('verified_quantity', 8, 2)->default(0)->after('is_verified');
            }
            if (!Schema::hasColumn('work_order_items', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_quantity');
            }
            if (!Schema::hasColumn('work_order_items', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            }
        });

        // 3. Tambah tracking verifikasi keseluruhan part pada work_orders
        Schema::table('work_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('work_orders', 'parts_verified_at')) {
                $table->timestamp('parts_verified_at')->nullable()->after('completed_at');
            }
            if (!Schema::hasColumn('work_orders', 'parts_verified_by')) {
                $table->foreignId('parts_verified_by')->nullable()->after('parts_verified_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            if (Schema::hasColumn('work_orders', 'parts_verified_by')) {
                $table->dropForeign(['parts_verified_by']);
                $table->dropColumn(['parts_verified_by', 'parts_verified_at']);
            }
        });

        Schema::table('work_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('work_order_items', 'verified_by')) {
                $table->dropForeign(['verified_by']);
                $table->dropColumn(['verified_by', 'verified_at', 'verified_quantity', 'is_verified']);
            }
        });

        Schema::table('parts', function (Blueprint $table) {
            if (Schema::hasColumn('parts', 'barcode')) {
                $table->dropColumn('barcode');
            }
        });
    }
};
