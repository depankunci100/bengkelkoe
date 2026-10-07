<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Part;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierReturn;
use App\Models\SupplierSales;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimBengkelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIM BENGKEL');
    }

    public function test_quick_login_works(): void
    {
        $response = $this->get('/login/quick/owner');
        $response->assertRedirect('/dashboard');
    }

    public function test_dashboard_renders_for_owner(): void
    {
        $owner = User::where('role', 'owner')->first();
        $response = $this->actingAs($owner)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Utama');
    }

    public function test_work_orders_index_renders(): void
    {
        $owner = User::where('role', 'owner')->first();
        $response = $this->actingAs($owner)->get('/work-orders');
        $response->assertStatus(200);
        $response->assertSee('Work Order');
    }

    public function test_customer_approval_public_portal(): void
    {
        $wo = WorkOrder::where('status', 'WAITING_APPROVAL')->first();
        $this->assertNotNull($wo);

        $response = $this->get('/approval/' . $wo->approval_token);
        $response->assertStatus(200);
        $response->assertSee('Portal Persetujuan Pelanggan');
        $response->assertSee('Hasil Pemeriksaan');
    }

    public function test_rest_api_endpoint(): void
    {
        $response = $this->getJson('/api/v1/work-orders');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
    }

    public function test_stock_opname_with_price_variance_moving_average(): void
    {
        $owner = User::where('role', 'owner')->first();
        $part = \App\Models\Part::create([
            'part_number' => 'TEST-OIL-01',
            'name' => 'Test Oli 10W-40',
            'brand' => 'Shell',
            'category' => 'Pelumas',
            'cost_price' => 100000,
            'selling_price' => 150000,
            'stock' => 10,
            'min_stock' => 5,
            'unit' => 'Liter',
            'supplier' => 'PT Supplier Lama',
        ]);

        // Masuk 10 liter lagi dengan harga beli baru 120.000 (Metode Weighted Moving Average)
        // Hitungan: ((10 * 100.000) + (10 * 120.000)) / 20 = 110.000
        $response = $this->actingAs($owner)->post("/parts/{$part->id}/adjust-stock", [
            'type' => 'IN',
            'quantity' => 10,
            'cost_price' => 120000,
            'cost_calculation_method' => 'AVERAGE',
            'batch_reference' => 'INV-PO-999',
            'supplier' => 'PT Distributor Baru',
            'notes' => 'Restock harga naik',
        ]);

        $response->assertRedirect();
        
        $part->refresh();
        $this->assertEquals(20, $part->stock);
        $this->assertEquals(110000, $part->cost_price);

        $lastMovement = \App\Models\StockMovement::where('part_id', $part->id)->latest('id')->first();
        $this->assertNotNull($lastMovement);
        $this->assertEquals(120000, $lastMovement->cost_price);
        $this->assertEquals('INV-PO-999', $lastMovement->batch_reference);
        $this->assertEquals('PT Distributor Baru', $lastMovement->supplier);

        // Kunjungi halaman detail suku cadang
        $detailResponse = $this->actingAs($owner)->get("/parts/{$part->id}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('INV-PO-999');
        $detailResponse->assertSee('PT Distributor Baru');
    }

    public function test_master_supplier_crud_and_view(): void
    {
        $owner = User::where('role', 'owner')->first();

        // 1. Index
        $response = $this->actingAs($owner)->get('/suppliers');
        $response->assertStatus(200);
        $response->assertSee('Master Supplier');
        $response->assertSee('PT Sumber Jaya Otomotif');

        // 2. Store new supplier
        $storeResponse = $this->actingAs($owner)->post('/suppliers', [
            'code' => 'SUP-999',
            'name' => 'PT Vendor Uji Coba',
            'contact_person' => 'Joko',
            'phone' => '081299999999',
            'email' => 'joko@ujicoba.com',
            'address' => 'Jl. Uji No. 1',
            'notes' => 'Tempo 14 Hari',
            'is_active' => true,
        ]);
        $storeResponse->assertRedirect('/suppliers');

        $supplier = \App\Models\Supplier::where('code', 'SUP-999')->first();
        $this->assertNotNull($supplier);

        // 3. Detail Show
        $showResponse = $this->actingAs($owner)->get("/suppliers/{$supplier->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('PT Vendor Uji Coba');
        $showResponse->assertSee('081299999999');
    }

    public function test_master_part_categories_and_spareparts_by_category(): void
    {
        $owner = User::where('role', 'owner')->first();

        // 1. Index Kategori
        $catResponse = $this->actingAs($owner)->get('/part-categories');
        $catResponse->assertStatus(200);
        $catResponse->assertSee('Master Kategori Suku Cadang');
        $catResponse->assertSee('Kaki-Kaki &amp; Suspensi', false);
        $catResponse->assertSee('Mesin (Engine Parts)');
        $catResponse->assertSee('Transmisi &amp; Drivetrain', false);

        // 2. Filter Spareparts Kaki-kaki
        $kakiResponse = $this->actingAs($owner)->get('/parts?category=' . urlencode('Kaki-Kaki & Suspensi'));
        $kakiResponse->assertStatus(200);
        $kakiResponse->assertSee('Shockbreaker Depan Avanza');
        $kakiResponse->assertSee('Ball Joint Lower Depan Avanza');
        $kakiResponse->assertSee('Tie Rod End Luar Avanza');

        // 3. Filter Spareparts Transmisi
        $trnResponse = $this->actingAs($owner)->get('/parts?category=' . urlencode('Transmisi & Drivetrain'));
        $trnResponse->assertStatus(200);
        $trnResponse->assertSee('Kampas Kopling (Clutch Disc) Avanza 1.3');
        $trnResponse->assertSee('Matahari Kopling / Dekrup (Clutch Cover) Avanza 1.3');
        $trnResponse->assertSee('Release Bearing (Driyep) Kopling Avanza');

        // 4. Filter Spareparts Mesin
        $engResponse = $this->actingAs($owner)->get('/parts?category=' . urlencode('Mesin (Engine Parts)'));
        $engResponse->assertStatus(200);
        $engResponse->assertSee('Paking Tutup Klep');
        $engResponse->assertSee('Water Pump Radiator');
    }

    public function test_supplier_can_have_multiple_sales_and_history_pengiriman(): void
    {
        $owner = User::where('role', 'owner')->first();
        $supplier = \App\Models\Supplier::where('code', 'SUP-001')->first();
        $this->assertNotNull($supplier);

        // 1. Verifikasi Supplier memiliki beberapa sales (dari seeder minimal 3)
        $this->assertGreaterThanOrEqual(2, $supplier->sales()->count());

        // 2. Tambah sales baru melalui POST /suppliers/{id}/sales
        $addSalesResponse = $this->actingAs($owner)->post("/suppliers/{$supplier->id}/sales", [
            'name' => 'Doni Saputra (Sales Test)',
            'phone' => '081288887777',
            'email' => 'doni@sumberjayaoto.com',
            'area' => 'Sidoarjo Kota',
            'notes' => 'Sales pelumas & filter',
            'is_active' => true,
        ]);
        $addSalesResponse->assertRedirect();

        $newSales = \App\Models\SupplierSales::where('name', 'Doni Saputra (Sales Test)')->first();
        $this->assertNotNull($newSales);
        $this->assertEquals($supplier->id, $newSales->supplier_id);

        // 3. API endpoint get sales by supplier
        $apiResponse = $this->actingAs($owner)->getJson("/api/suppliers/{$supplier->id}/sales");
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment(['name' => 'Doni Saputra (Sales Test)']);

        // 4. Verifikasi halaman customer Work Order TIDAK menampilkan dropdown supplier/sales
        $woCreateResponse = $this->actingAs($owner)->get('/work-orders/create');
        $woCreateResponse->assertStatus(200);
        $woCreateResponse->assertDontSee('Mitra Supplier & Sales Penanggung Jawab');

        $customer = \App\Models\Customer::first();
        $vehicle = $customer->vehicles()->first();
        $wo = WorkOrder::create([
            'wo_number' => WorkOrder::generateWoNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'DRAFT',
            'complaint' => 'Servis berkala pelanggan umum',
        ]);
        $woShowResponse = $this->actingAs($owner)->get("/work-orders/{$wo->id}");
        $woShowResponse->assertStatus(200);
        $woShowResponse->assertDontSee('Mitra Supplier & Sales yang Melakukan WO');

        // 5. Catat pengiriman barang dari sales ini ke bengkel (History Pengiriman via Stock In)
        $part = $supplier->parts()->first();
        $adjustResponse = $this->actingAs($owner)->post("/parts/{$part->id}/adjust-stock", [
            'type' => 'IN',
            'quantity' => 15,
            'cost_price' => 250000,
            'batch_reference' => 'SJ-DELIVERY-TEST-99',
            'supplier_id' => $supplier->id,
            'supplier_sales_id' => $newSales->id,
            'notes' => 'Pengiriman perdana sales Doni',
        ]);
        $adjustResponse->assertRedirect();

        // 6. Verifikasi di halaman detail Supplier menampilkan sales dan tab History Pengiriman
        $supplierShowResponse = $this->actingAs($owner)->get("/suppliers/{$supplier->id}");
        $supplierShowResponse->assertStatus(200);
        $supplierShowResponse->assertSee('Doni Saputra (Sales Test)');
        $supplierShowResponse->assertSee('History Pengiriman Setiap Sales ke Bengkel');
        $supplierShowResponse->assertSee('SJ-DELIVERY-TEST-99');
        $supplierShowResponse->assertSee('Pengiriman perdana sales Doni');
    }

    public function test_invoice_creation_with_fixed_discount(): void
    {
        $owner = User::where('role', 'owner')->first();
        $customer = \App\Models\Customer::first();
        $vehicle = $customer->vehicles()->first();

        // 1. Buat Work Order baru dengan beberapa item
        $wo = WorkOrder::create([
            'wo_number' => WorkOrder::generateWoNumber(),
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'APPROVED',
            'complaint' => 'Servis besar uji diskon',
            'subtotal' => 1000000,
            'discount' => 0,
            'tax' => 110000,
            'grand_total' => 1110000,
        ]);

        // Tambah item jasa
        \App\Models\WorkOrderItem::create([
            'work_order_id' => $wo->id,
            'type' => 'SERVICE',
            'item_name' => 'Paket Servis Tune Up',
            'quantity' => 1,
            'unit_price' => 1000000,
            'subtotal' => 1000000,
            'approval_status' => 'APPROVED',
        ]);
        $wo->recalculateTotals();

        // 2. Terbitkan Invoice dengan Diskon Nominal Rp 100.000
        $response = $this->actingAs($owner)->post('/invoices', [
            'work_order_id' => $wo->id,
            'discount_type' => 'FIXED',
            'discount_value' => 100000,
            'discount_reason' => 'Promo Spesial Pembukaan',
            'include_tax' => 1,
            'notes' => 'Faktur dengan diskon nominal Rp 100.000',
        ]);

        $response->assertRedirect();

        $invoice = \App\Models\Invoice::where('work_order_id', $wo->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(1000000, (float)$invoice->subtotal);
        $this->assertEquals(100000, (float)$invoice->discount);
        $this->assertEquals('FIXED', $invoice->discount_type);
        $this->assertEquals(10.0, (float)$invoice->discount_percent);
        $this->assertEquals('Promo Spesial Pembukaan', $invoice->discount_reason);
        // DPP: 900.000 -> PPN 11%: 99.000 -> Grand Total: 999.000
        $this->assertEquals(99000, (float)$invoice->tax);
        $this->assertEquals(999000, (float)$invoice->grand_total);
        $this->assertEquals(999000, (float)$invoice->balance_due);

        // 3. Verifikasi tampilan web invoice & cetak
        $showResponse = $this->actingAs($owner)->get("/invoices/{$invoice->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Promo Spesial Pembukaan');
        $showResponse->assertSee(number_format(100000, 0, ',', '.'));

        $printResponse = $this->actingAs($owner)->get("/invoices/{$invoice->id}/print");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Promo Spesial Pembukaan');

        // 4. Ubah Diskon Invoice sebelum pembayaran (Update Diskon ke Persentase 15%)
        // Subtotal 1.000.000, diskon 15% = 150.000. DPP = 850.000. PPN 11% = 93.500. Grand Total = 943.500
        $updateResponse = $this->actingAs($owner)->put("/invoices/{$invoice->id}/discount", [
            'discount_type' => 'PERCENT',
            'discount_value' => 15,
            'discount_reason' => 'Diskon Tambahan Member VIP 15%',
            'include_tax' => 1,
        ]);
        $updateResponse->assertRedirect();

        $invoice->refresh();
        $this->assertEquals(150000, (float)$invoice->discount);
        $this->assertEquals('PERCENT', $invoice->discount_type);
        $this->assertEquals(15.0, (float)$invoice->discount_percent);
        $this->assertEquals(93500, (float)$invoice->tax);
        $this->assertEquals(943500, (float)$invoice->grand_total);
        $this->assertEquals(943500, (float)$invoice->balance_due);
    }

    public function test_supplier_work_order_stock_in_with_sales_and_damaged_part_return(): void
    {
        $owner = User::where('role', 'owner')->first();
        $supplier = \App\Models\Supplier::first();
        $sales = $supplier->sales()->first();
        $this->assertNotNull($sales);

        // 1. Buat Part Baru
        $part = \App\Models\Part::create([
            'part_number' => 'SHK-TEST-RETUR-01',
            'name' => 'Shockbreaker Depan Uji Retur',
            'brand' => 'Kayaba',
            'category' => 'Kaki-kaki',
            'cost_price' => 300000,
            'selling_price' => 450000,
            'stock' => 0,
            'min_stock' => 2,
            'unit' => 'Pcs',
            'supplier_id' => $supplier->id,
            'supplier' => $supplier->name,
        ]);

        // 2. Pencatatan Barang Masuk (WO Supplier / Stock Opname) lengkap dengan Sales Penanggung Jawab & No. Batch
        $adjustResponse = $this->actingAs($owner)->post("/parts/{$part->id}/adjust-stock", [
            'type' => 'IN',
            'quantity' => 10,
            'cost_price' => 300000,
            'batch_reference' => 'SJ-KYB-2026-88',
            'supplier_id' => $supplier->id,
            'supplier_sales_id' => $sales->id,
            'notes' => 'Penerimaan stok batch dari sales ' . $sales->name,
        ]);
        $adjustResponse->assertRedirect();

        $part->refresh();
        $this->assertEquals(10, $part->stock);

        $movement = \App\Models\StockMovement::where('part_id', $part->id)->latest()->first();
        $this->assertNotNull($movement);
        $this->assertEquals('SJ-KYB-2026-88', $movement->batch_reference);
        $this->assertEquals($sales->id, $movement->supplier_sales_id);
        $this->assertEquals($supplier->id, $movement->supplier_id);

        // 3. Terjadi barang rusak pada batch tersebut (2 pcs bocor oli) -> Buat Faktur Pengembalian Barang ke Supplier (Retur)
        $returnResponse = $this->actingAs($owner)->post('/supplier-returns', [
            'part_id' => $part->id,
            'supplier_id' => $supplier->id,
            'supplier_sales_id' => $sales->id,
            'stock_movement_id' => $movement->id,
            'batch_reference' => 'SJ-KYB-2026-88',
            'quantity' => 2,
            'cost_price' => 300000,
            'settlement_type' => 'REPLACEMENT',
            'reason' => 'Bocor oli saat unboxing dari batch 88',
            'notes' => 'Segera hubungi sales untuk unit pengganti baru',
        ]);
        $returnResponse->assertRedirect();

        // 4. Verifikasi stok fisik berkurang karena 2 unit rusak dikeluarkan untuk retur (10 - 2 = 8)
        $part->refresh();
        $this->assertEquals(8, $part->stock);

        $return = \App\Models\SupplierReturn::where('part_id', $part->id)->first();
        $this->assertNotNull($return);
        $this->assertEquals(600000, (float)$return->total_amount); // 2 x 300.000
        $this->assertEquals('PENDING', $return->status);
        $this->assertEquals($sales->id, $return->supplier_sales_id);
        $this->assertEquals($supplier->id, $return->supplier_id);
        $this->assertEquals('SJ-KYB-2026-88', $return->batch_reference);

        // 5. Verifikasi tampilan web detail faktur retur & cetak
        $showResponse = $this->actingAs($owner)->get("/supplier-returns/{$return->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertSee($sales->name);
        $showResponse->assertSee('SJ-KYB-2026-88');
        $showResponse->assertSee('Bocor oli saat unboxing dari batch 88');

        $printResponse = $this->actingAs($owner)->get("/supplier-returns/{$return->id}/print");
        $printResponse->assertStatus(200);
        $printResponse->assertSee($return->return_number);
        $printResponse->assertSee($sales->name);

        // 6. Update Status ke COMPLETED (Unit pengganti baru diterima dari Sales)
        // Stok harus otomatis bertambah kembali (8 + 2 = 10)
        $statusResponse = $this->actingAs($owner)->post("/supplier-returns/{$return->id}/status", [
            'status' => 'COMPLETED',
            'notes' => 'Unit baru telah dikirimkan sales pengganti',
        ]);
        $statusResponse->assertRedirect();

        $return->refresh();
        $this->assertEquals('COMPLETED', $return->status);
        $this->assertNotNull($return->resolved_at);

        $part->refresh();
        $this->assertEquals(10, $part->stock); // Kembali 10 unit karena replacement masuk
    }

    public function test_barcode_scanner_lookup_endpoint()
    {
        $user = User::where('role', 'admin')->first();
        $part = Part::create([
            'part_number' => 'TST-BC-001',
            'barcode' => '8991234567890',
            'name' => 'Busi Iridium Super',
            'brand' => 'NGK',
            'category' => 'Mesin',
            'cost_price' => 45000,
            'selling_price' => 65000,
            'stock' => 15,
            'min_stock' => 5,
            'unit' => 'Pcs',
            'location' => 'Rak A-01',
            'is_active' => true,
        ]);

        // 1. Lookup by Barcode
        $response = $this->actingAs($user)->getJson(route('scanner.lookup', ['code' => '8991234567890']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'part' => [
                'id' => $part->id,
                'part_number' => 'TST-BC-001',
                'name' => 'Busi Iridium Super',
                'stock' => 15,
            ]
        ]);

        // 2. Lookup by Part Number
        $response = $this->actingAs($user)->getJson(route('scanner.lookup', ['code' => 'TST-BC-001']));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 3. Lookup non-existent
        $response = $this->actingAs($user)->getJson(route('scanner.lookup', ['code' => 'NOT-EXIST-999']));
        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    public function test_barcode_scanner_stock_opname_and_stock_in()
    {
        $user = User::where('role', 'admin')->first();
        $supplier = Supplier::create([
            'code' => 'SUP-ASTRA-01',
            'name' => 'PT Astra Auto',
            'phone' => '0812345678',
            'is_active' => true,
        ]);
        $sales = SupplierSales::create([
            'supplier_id' => $supplier->id,
            'name' => 'Rudi Salim',
            'phone' => '0819876543',
            'is_active' => true,
        ]);

        $part = Part::create([
            'part_number' => 'PRT-FLT-TEST',
            'barcode' => '8999988881111',
            'name' => 'Filter Bensin Universal',
            'brand' => 'Denso',
            'category' => 'Mesin',
            'stock' => 10,
            'min_stock' => 2,
            'unit' => 'Pcs',
            'cost_price' => 50000,
            'selling_price' => 75000,
            'is_active' => true,
        ]);

        // 1. Test Stock Opname (Audit Fisik: sistem 10, fisik di rak ternyata 14)
        $opnameRes = $this->actingAs($user)->postJson(route('scanner.opname'), [
            'part_id' => $part->id,
            'physical_stock' => 14,
            'notes' => 'Audit rak A1 via scanner',
        ]);

        $opnameRes->assertStatus(200);
        $opnameRes->assertJson(['success' => true, 'diff' => 4]);
        $part->refresh();
        $this->assertEquals(14, $part->stock);

        // 2. Test Stock IN via Scanner dengan Sales PIC & Batch
        $stockInRes = $this->actingAs($user)->postJson(route('scanner.stock-in'), [
            'part_id' => $part->id,
            'quantity' => 6,
            'cost_price' => 52000,
            'supplier_id' => $supplier->id,
            'supplier_sales_id' => $sales->id,
            'batch_reference' => 'SJ-202610-ASTRA',
            'notes' => 'Penerimaan barang barcode scan',
        ]);

        $stockInRes->assertStatus(200);
        $stockInRes->assertJson(['success' => true]);
        $part->refresh();
        $this->assertEquals(20, $part->stock); // 14 + 6 = 20
        $this->assertEquals(52000, $part->cost_price);

        // Pastikan StockMovement merekam sales ID dan batch
        $lastMovement = StockMovement::where('part_id', $part->id)->latest('id')->first();
        $this->assertEquals('IN', $lastMovement->type);
        $this->assertEquals($sales->id, $lastMovement->supplier_sales_id);
        $this->assertEquals('SJ-202610-ASTRA', $lastMovement->batch_reference);
    }

    public function test_barcode_scanner_work_order_dispatch_verification()
    {
        $user = User::where('role', 'admin')->first();
        $customer = Customer::first();
        $vehicle = Vehicle::first();

        $partCorrect = Part::create([
            'part_number' => 'PRT-CORRECT-01',
            'barcode' => '777888999111',
            'name' => 'Filter Oli Asli',
            'brand' => 'Denso',
            'category' => 'Mesin',
            'cost_price' => 50000,
            'selling_price' => 75000,
            'stock' => 20,
            'min_stock' => 5,
            'unit' => 'Pcs',
            'is_active' => true,
        ]);

        $partWrong = Part::create([
            'part_number' => 'PRT-WRONG-99',
            'barcode' => '000111222333',
            'name' => 'Kampas Rem Belakang (Salah)',
            'brand' => 'Brembo',
            'category' => 'Kaki-kaki',
            'cost_price' => 120000,
            'selling_price' => 160000,
            'stock' => 5,
            'min_stock' => 2,
            'unit' => 'Set',
            'is_active' => true,
        ]);

        $wo = WorkOrder::create([
            'wo_number' => 'WO-2026-SCAN',
            'customer_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => 'IN_PROGRESS',
            'complaint' => 'Servis berkala',
            'created_by' => $user->id,
        ]);

        $woItem = WorkOrderItem::create([
            'work_order_id' => $wo->id,
            'type' => 'PART',
            'part_id' => $partCorrect->id,
            'item_name' => $partCorrect->name,
            'quantity' => 2,
            'unit_price' => 75000,
            'subtotal' => 150000,
            'approval_status' => 'APPROVED',
            'is_verified' => false,
            'verified_quantity' => 0,
        ]);

        // 1. Scan barang yang SALAH (tidak ada di WO) -> Harus ditolak (422 NOT_IN_WORK_ORDER)
        $wrongRes = $this->actingAs($user)->postJson(route('scanner.verify-item'), [
            'work_order_id' => $wo->id,
            'barcode_or_code' => '000111222333',
            'quantity' => 1,
        ]);
        $wrongRes->assertStatus(422);
        $wrongRes->assertJson([
            'success' => false,
            'error_type' => 'NOT_IN_WORK_ORDER',
        ]);

        // 2. Scan barang yang BENAR (Scan ke-1: 1 dari 2)
        $correctRes1 = $this->actingAs($user)->postJson(route('scanner.verify-item'), [
            'work_order_id' => $wo->id,
            'barcode_or_code' => '777888999111',
            'quantity' => 1,
        ]);
        $correctRes1->assertStatus(200);
        $correctRes1->assertJson([
            'success' => true,
            'item' => [
                'verified_quantity' => 1,
                'is_verified' => false,
            ],
            'all_complete' => false,
        ]);

        // 3. Scan barang yang BENAR (Scan ke-2: 2 dari 2 -> COMPLETE!)
        $correctRes2 = $this->actingAs($user)->postJson(route('scanner.verify-item'), [
            'work_order_id' => $wo->id,
            'barcode_or_code' => 'PRT-CORRECT-01',
            'quantity' => 1,
        ]);
        $correctRes2->assertStatus(200);
        $correctRes2->assertJson([
            'success' => true,
            'item' => [
                'verified_quantity' => 2,
                'is_verified' => true,
            ],
            'all_complete' => true,
        ]);

        $wo->refresh();
        $this->assertNotNull($wo->parts_verified_at);
        $this->assertEquals($user->id, $wo->parts_verified_by);

        // 4. Test Cetak Bukti Pengeluaran Barang (Picking Slip)
        $slipRes = $this->actingAs($user)->get(route('work-orders.picking-slip', $wo->id));
        $slipRes->assertStatus(200);
        $slipRes->assertSee('BUKTI PENGELUARAN SUKU CADANG');
        $slipRes->assertSee($wo->wo_number);
        $slipRes->assertSee('Filter Oli Asli');

        // 5. Test Cetak Label Barcode Part
        $barcodeRes = $this->actingAs($user)->get(route('parts.barcode-print', $partCorrect->id));
        $barcodeRes->assertStatus(200);
        $barcodeRes->assertSee('Label Barcode');
        $barcodeRes->assertSee($partCorrect->part_number);
    }
}



