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
}
