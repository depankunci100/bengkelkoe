<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
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
}

