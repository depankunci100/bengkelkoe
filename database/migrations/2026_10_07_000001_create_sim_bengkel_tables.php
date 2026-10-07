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
        // 1. Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Vehicles
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('plate_number')->unique()->index();
            $table->string('brand');
            $table->string('model');
            $table->integer('year')->nullable();
            $table->string('transmission')->default('Automatic'); // Manual / Automatic
            $table->string('vin_number')->nullable();
            $table->string('color')->nullable();
            $table->integer('odometer')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Services (Jasa)
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('Umum'); // Perawatan Berkala, Mesin, Rem, Kelistrikan, AC, dll.
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('estimated_minutes')->default(60);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Parts (Spareparts)
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->unique()->index();
            $table->string('name');
            $table->string('brand')->default('Original');
            $table->string('category')->default('Oli & Cairan'); // Oli & Cairan, Filter, Rem, Mesin, Suspensi, dll.
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('unit')->default('Pcs');
            $table->string('location')->nullable(); // Rak A-1
            $table->string('supplier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Work Orders
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique()->index();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('DRAFT'); // DRAFT, INSPECTION, WAITING_APPROVAL, APPROVED, IN_PROGRESS, PAUSED, QC, READY_FOR_PICKUP, COMPLETED, REJECTED, CANCELLED
            $table->text('complaint');
            $table->integer('odometer_in')->nullable();
            $table->text('technician_notes')->nullable();
            $table->text('qc_notes')->nullable();
            $table->decimal('total_services', 12, 2)->default(0);
            $table->decimal('total_parts', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('approval_token', 64)->nullable()->unique();
            $table->string('approval_status')->default('NOT_REQUESTED'); // NOT_REQUESTED, PENDING, APPROVED, PARTIALLY_APPROVED, REJECTED, EXPIRED
            $table->timestamp('approval_sent_at')->nullable();
            $table->timestamp('approval_expires_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 6. Work Order Items
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // SERVICE, PART
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('part_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('approval_status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->boolean('is_additional')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Inspections
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('overall_summary')->nullable();
            $table->string('status')->default('IN_PROGRESS'); // IN_PROGRESS, COMPLETED
            $table->timestamps();
        });

        // 8. Inspection Items
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // ENGINE, BRAKE, ELECTRICAL, SUSPENSION, BODY_INTERIOR
            $table->string('item_name');
            $table->string('condition')->default('GOOD'); // GOOD, WARNING, BAD, NEED_REPLACEMENT
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        // 9. Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->index();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->string('payment_status')->default('UNPAID'); // UNPAID, PARTIAL, PAID
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });

        // 10. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number')->unique()->index();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('payment_method')->default('CASH'); // CASH, BANK_TRANSFER, QRIS, DEBIT_CREDIT
            $table->string('reference_number')->nullable();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 11. Stock Movements
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // IN, OUT, ADJUSTMENT
            $table->decimal('quantity', 8, 2);
            $table->decimal('before_stock', 8, 2);
            $table->decimal('after_stock', 8, 2);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 12. Work Order Timelines (Audit Trail Event Riil)
        Schema::create('wo_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        // 13. WhatsApp Logs
        Schema::create('whatsapp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('phone');
            $table->text('message');
            $table->string('status')->default('SENT'); // QUEUED, SENT, FAILED
            $table->timestamps();
        });

        // 14. Workshop Settings
        Schema::create('workshop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_settings');
        Schema::dropIfExists('whatsapp_logs');
        Schema::dropIfExists('wo_timelines');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('inspection_items');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('work_order_items');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('parts');
        Schema::dropIfExists('services');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('customers');
    }
};
