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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('discount_type', 20)->default('FIXED')->after('subtotal');
            $table->decimal('discount_percent', 5, 2)->nullable()->default(0)->after('discount_type');
            $table->string('discount_reason', 255)->nullable()->after('discount');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('discount_type', 20)->default('FIXED')->after('subtotal');
            $table->decimal('discount_percent', 5, 2)->nullable()->default(0)->after('discount_type');
            $table->string('discount_reason', 255)->nullable()->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_percent', 'discount_reason']);
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_percent', 'discount_reason']);
        });
    }
};
