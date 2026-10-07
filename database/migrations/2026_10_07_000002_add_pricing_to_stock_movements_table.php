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
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->nullable()->after('quantity');
            $table->decimal('selling_price', 12, 2)->nullable()->after('cost_price');
            $table->string('batch_reference')->nullable()->after('selling_price');
            $table->string('supplier')->nullable()->after('batch_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'selling_price', 'batch_reference', 'supplier']);
        });
    }
};
