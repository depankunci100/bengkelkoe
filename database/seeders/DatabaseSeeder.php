<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\Invoice;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\Payment;
use App\Models\Service;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierSales;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WhatsappLog;
use App\Models\WorkshopSetting;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Models\WoTimeline;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Settings
        $settings = [
            'workshop_name' => 'SIM BENGKEL AUTO SERVICE',
            'workshop_tagline' => 'Bengkel Mobil Profesional & Terpercaya',
            'workshop_address' => 'Jl. Raya Otomotif No. 88, Surabaya, Jawa Timur',
            'workshop_phone' => '031-8976543',
            'workshop_email' => 'admin@simbengkel.com',
            'whatsapp_number' => '081234567890',
            'primary_color' => '#0d6efd',
            'tax_rate' => '11',
            'invoice_footer' => 'Terima kasih atas kunjungan Anda. Garansi servis pengerjaan 14 hari atau 1.000 KM.',
        ];
        foreach ($settings as $key => $val) {
            WorkshopSetting::create(['key' => $key, 'value' => $val]);
        }

        // 2. Users
        $owner = User::create([
            'name' => 'Bambang Wijaya (Owner)',
            'email' => 'owner@bengkel.com',
            'role' => 'owner',
            'phone' => '081122334455',
            'specialization' => 'Kepala Bengkel',
            'password' => Hash::make('password'),
        ]);

        $admin = User::create([
            'name' => 'Siti Rahma (Kasir/Admin)',
            'email' => 'admin@bengkel.com',
            'role' => 'admin',
            'phone' => '081233445566',
            'specialization' => 'Front Office & Keuangan',
            'password' => Hash::make('password'),
        ]);

        $tech1 = User::create([
            'name' => 'Agus Pratama (Senior Mekanik)',
            'email' => 'teknisi@bengkel.com',
            'role' => 'technician',
            'phone' => '081344556677',
            'specialization' => 'Mesin & Transmisi',
            'password' => Hash::make('password'),
        ]);

        $tech2 = User::create([
            'name' => 'Doni Santoso (Mekanik Kelistrikan)',
            'email' => 'doni@bengkel.com',
            'role' => 'technician',
            'phone' => '081455667788',
            'specialization' => 'Kelistrikan & AC',
            'password' => Hash::make('password'),
        ]);

        $tech3 = User::create([
            'name' => 'Hendra Setiawan (Mekanik Kaki-kaki)',
            'email' => 'hendra@bengkel.com',
            'role' => 'technician',
            'phone' => '081566778899',
            'specialization' => 'Kaki-kaki & Spooring',
            'password' => Hash::make('password'),
        ]);

        // 3. Services (Jasa)
        $services = [
            ['code' => 'SRV-001', 'name' => 'Ganti Oli Mesin & Filter', 'category' => 'Perawatan Berkala', 'price' => 75000, 'estimated_minutes' => 30],
            ['code' => 'SRV-002', 'name' => 'Tune Up Mesin Injeksi', 'category' => 'Mesin', 'price' => 250000, 'estimated_minutes' => 60],
            ['code' => 'SRV-003', 'name' => 'Overhaul & Servis Rem 4 Roda', 'category' => 'Rem & Kaki-kaki', 'price' => 200000, 'estimated_minutes' => 60],
            ['code' => 'SRV-004', 'name' => 'Servis AC Lengkap + Fogging', 'category' => 'Sistem AC', 'price' => 450000, 'estimated_minutes' => 90],
            ['code' => 'SRV-005', 'name' => 'Spooring 3D & Balancing 4 Roda', 'category' => 'Kaki-kaki & Ban', 'price' => 220000, 'estimated_minutes' => 45],
            ['code' => 'SRV-006', 'name' => 'Flushing Radiator Coolant', 'category' => 'Sistem Pendingin', 'price' => 90000, 'estimated_minutes' => 30],
            ['code' => 'SRV-007', 'name' => 'Pembersihan Throttle Body & Intake', 'category' => 'Mesin', 'price' => 120000, 'estimated_minutes' => 40],
            ['code' => 'SRV-008', 'name' => 'General Checkup & Diagnosa Scanner OBD2', 'category' => 'Diagnosa', 'price' => 150000, 'estimated_minutes' => 45],
        ];
        $svcModels = [];
        foreach ($services as $svc) {
            $svcModels[$svc['code']] = Service::create($svc);
        }

        // 4. Master Suppliers
        $suppliersData = [
            [
                'code' => 'SUP-001',
                'name' => 'PT Sumber Jaya Otomotif',
                'contact_person' => 'Bambang Sudiro (Sales)',
                'phone' => '081234560001',
                'email' => 'sales@sumberjayaoto.com',
                'address' => 'Jl. Bubutan No. 45, Surabaya',
                'notes' => 'Distributor Utama Denso, Toyota Genuine, & Aisin. TOP 30 Hari.',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-002',
                'name' => 'Toko Kaki-Kaki Mandiri',
                'contact_person' => 'Hendro Setiawan (Toko)',
                'phone' => '081234560002',
                'email' => 'kakikakimandiri@gmail.com',
                'address' => 'Pusat Onderdil Kedungdoro Blok B-12, Surabaya',
                'notes' => 'Spesialis Kaki-kaki: Kayaba, 555 Sankei Japan, RBI Bushing, Koyo Bearing.',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-003',
                'name' => 'CV Bintang Transmisi & Kopling',
                'contact_person' => 'Rudi Hartono (Gudang)',
                'phone' => '081234560003',
                'email' => 'bintangtransmisi@yahoo.com',
                'address' => 'Jl. Demak No. 102, Surabaya',
                'notes' => 'Spesialis Kopling Manual, Transmisi Matik, CV Joint As Roda: Aisin, NSK, NKN.',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-004',
                'name' => 'PT Pelumas Nusantara Sentosa',
                'contact_person' => 'Iwan Setiadi',
                'phone' => '081234560004',
                'email' => 'order@pelumasnusantara.co.id',
                'address' => 'Kawasan Industri Rungkut Blok C-5, Surabaya',
                'notes' => 'Distributor Resmi Castrol, Shell, Pertamina, Prestone.',
                'is_active' => true,
            ],
            [
                'code' => 'SUP-005',
                'name' => 'CV Berkat Rem Otomotif',
                'contact_person' => 'Agus Santoso',
                'phone' => '081234560005',
                'email' => 'berkatrem@gmail.com',
                'address' => 'Jl. Tidar No. 88, Surabaya',
                'notes' => 'Distributor Kampas Rem & Disc Brake: Akebono, Bendix, Brembo.',
                'is_active' => true,
            ],
        ];

        $supModels = [];
        foreach ($suppliersData as $sup) {
            $supModels[$sup['code']] = Supplier::create($sup);
        }

        // Master Sales Representatif per Supplier (1 Supplier punya banyak Sales)
        $salesData = [
            // Sales PT Sumber Jaya Otomotif
            [
                'supplier_id' => $supModels['SUP-001']->id,
                'name' => 'Bambang Sudiro',
                'phone' => '081234560001',
                'email' => 'bambang@sumberjayaoto.com',
                'area' => 'Surabaya Barat & Pusat',
                'notes' => 'Sales Senior sparepart Toyota Genuine & Denso.',
            ],
            [
                'supplier_id' => $supModels['SUP-001']->id,
                'name' => 'Denny Setiawan',
                'phone' => '081234560011',
                'email' => 'denny@sumberjayaoto.com',
                'area' => 'Surabaya Timur & Sidoarjo',
                'notes' => 'Spesialis Part Fast Moving & Busi.',
            ],
            [
                'supplier_id' => $supModels['SUP-001']->id,
                'name' => 'Riko Pratama',
                'phone' => '081234560012',
                'email' => 'riko@sumberjayaoto.com',
                'area' => 'Surabaya Utara & Gresik',
                'notes' => 'Spesialis Kopling Aisin & Pompa Radiator.',
            ],

            // Sales Toko Kaki-Kaki Mandiri
            [
                'supplier_id' => $supModels['SUP-002']->id,
                'name' => 'Hendro Setiawan',
                'phone' => '081234560002',
                'email' => 'hendro@kakikakimandiri.com',
                'area' => 'Sales Counter Pusat',
                'notes' => 'Admin order toko Kedungdoro.',
            ],
            [
                'supplier_id' => $supModels['SUP-002']->id,
                'name' => 'Aris Munandar',
                'phone' => '081234560021',
                'email' => 'aris@kakikakimandiri.com',
                'area' => 'Sales Lapangan / Delivery Understeel',
                'notes' => 'Sering antar langsung shock Kayaba & Tierod 555 ke bengkel.',
            ],

            // Sales CV Bintang Transmisi & Kopling
            [
                'supplier_id' => $supModels['SUP-003']->id,
                'name' => 'Rudi Hartono',
                'phone' => '081234560003',
                'email' => 'rudi@bintangtransmisi.com',
                'area' => 'Area Bengkel Rekanan Surabaya',
                'notes' => 'Penanggung jawab garansi kopling Aisin.',
            ],
            [
                'supplier_id' => $supModels['SUP-003']->id,
                'name' => 'Ferry Irawan',
                'phone' => '081234560031',
                'email' => 'ferry@bintangtransmisi.com',
                'area' => 'Spesialis Transmisi Matik & CV Joint',
                'notes' => 'Biasa bawa mobil dinas atau armada toko untuk WO overhaul transmisi.',
            ],

            // Sales PT Pelumas Nusantara Sentosa
            [
                'supplier_id' => $supModels['SUP-004']->id,
                'name' => 'Iwan Setiadi',
                'phone' => '081234560004',
                'email' => 'iwan@pelumasnusantara.co.id',
                'area' => 'Sales Representative Pelumas Jawa Timur',
                'notes' => 'Distribusi rutin drum & galon Castrol/Shell.',
            ],
        ];

        $salesModels = [];
        foreach ($salesData as $idx => $sData) {
            $salesModels[$idx] = SupplierSales::create($sData);
        }

        // 5. Master Kategori Suku Cadang
        $categoriesData = [
            [
                'code' => 'CAT-SUS',
                'name' => 'Kaki-Kaki & Suspensi',
                'description' => 'Komponen understeel, shockbreaker, tierod, balljoint, bushing arm, bearing roda',
                'icon' => 'bi-arrows-collapse',
                'is_active' => true,
            ],
            [
                'code' => 'CAT-ENG',
                'name' => 'Mesin (Engine Parts)',
                'description' => 'Komponen blok mesin, piston, klep, packing/gasket, timing belt, water pump, busi',
                'icon' => 'bi-gear',
                'is_active' => true,
            ],
            [
                'code' => 'CAT-TRN',
                'name' => 'Transmisi & Drivetrain',
                'description' => 'Kopling set (plat, dekrup, bearing), master kopling, as roda / CV Joint, girboks',
                'icon' => 'bi-shuffle',
                'is_active' => true,
            ],
            [
                'code' => 'CAT-BRK',
                'name' => 'Sistem Pengereman',
                'description' => 'Brake pad kampas rem cakram, brake shoe tromol, piringan disc, caliper, master rem',
                'icon' => 'bi-disc',
                'is_active' => true,
            ],
            [
                'code' => 'CAT-OIL',
                'name' => 'Oli & Cairan Kimia',
                'description' => 'Oli mesin, oli transmisi manual & matik, air radiator coolant, minyak rem',
                'icon' => 'bi-droplet-half',
                'is_active' => true,
            ],
            [
                'code' => 'CAT-FLT',
                'name' => 'Filter (Penyaring)',
                'description' => 'Filter oli mesin, filter udara intake, filter kabin AC, filter bensin',
                'icon' => 'bi-funnel',
                'is_active' => true,
            ],
            [
                'code' => 'CAT-ELC',
                'name' => 'Kelistrikan & Sensor',
                'description' => 'Aki / baterai, dinamo starter, alternator, bohlam lampu, sekring, sensor OBD',
                'icon' => 'bi-lightning-charge',
                'is_active' => true,
            ],
        ];

        $catModels = [];
        foreach ($categoriesData as $cat) {
            $catModels[$cat['code']] = PartCategory::create($cat);
        }

        // 6. Parts (Spareparts Komprehensif: Kaki-kaki, Mesin, Transmisi, dll.)
        $parts = [
            // --- OLI & CAIRAN ---
            [
                'part_number' => 'PRT-OIL-01',
                'name' => 'Oli Castrol Magnatec 10W-40 (4L)',
                'brand' => 'Castrol',
                'category' => 'Oli & Cairan Kimia',
                'part_category_id' => $catModels['CAT-OIL']->id,
                'supplier_id' => $supModels['SUP-004']->id,
                'supplier' => $supModels['SUP-004']->name,
                'cost_price' => 310000,
                'selling_price' => 385000,
                'stock' => 16,
                'min_stock' => 5,
                'unit' => 'Galon',
                'location' => 'Rak A-1',
            ],
            [
                'part_number' => 'PRT-OIL-02',
                'name' => 'Oli Shell Helix Ultra 5W-30 Full Syn (4L)',
                'brand' => 'Shell',
                'category' => 'Oli & Cairan Kimia',
                'part_category_id' => $catModels['CAT-OIL']->id,
                'supplier_id' => $supModels['SUP-004']->id,
                'supplier' => $supModels['SUP-004']->name,
                'cost_price' => 450000,
                'selling_price' => 560000,
                'stock' => 8,
                'min_stock' => 4,
                'unit' => 'Galon',
                'location' => 'Rak A-2',
            ],
            [
                'part_number' => 'PRT-CLT-01',
                'name' => 'Air Radiator Coolant Prestone 4L',
                'brand' => 'Prestone',
                'category' => 'Oli & Cairan Kimia',
                'part_category_id' => $catModels['CAT-OIL']->id,
                'supplier_id' => $supModels['SUP-004']->id,
                'supplier' => $supModels['SUP-004']->name,
                'cost_price' => 85000,
                'selling_price' => 120000,
                'stock' => 14,
                'min_stock' => 5,
                'unit' => 'Galon',
                'location' => 'Rak A-4',
            ],

            // --- FILTER ---
            [
                'part_number' => 'PRT-FLT-01',
                'name' => 'Filter Oli Toyota Avanza / Xenia / Rush',
                'brand' => 'Denso',
                'category' => 'Filter (Penyaring)',
                'part_category_id' => $catModels['CAT-FLT']->id,
                'supplier_id' => $supModels['SUP-001']->id,
                'supplier' => $supModels['SUP-001']->name,
                'cost_price' => 32000,
                'selling_price' => 48000,
                'stock' => 3,
                'min_stock' => 5,
                'unit' => 'Pcs',
                'location' => 'Rak B-1',
            ], // LOW STOCK
            [
                'part_number' => 'PRT-FLT-02',
                'name' => 'Filter Oli Innova Bensin / Diesel',
                'brand' => 'Toyota Genuine',
                'category' => 'Filter (Penyaring)',
                'part_category_id' => $catModels['CAT-FLT']->id,
                'supplier_id' => $supModels['SUP-001']->id,
                'supplier' => $supModels['SUP-001']->name,
                'cost_price' => 45000,
                'selling_price' => 65000,
                'stock' => 12,
                'min_stock' => 5,
                'unit' => 'Pcs',
                'location' => 'Rak B-1',
            ],

            // --- PENGEREMAN ---
            [
                'part_number' => 'PRT-BRK-01',
                'name' => 'Brake Pad Depan Toyota Avanza',
                'brand' => 'Akebono',
                'category' => 'Sistem Pengereman',
                'part_category_id' => $catModels['CAT-BRK']->id,
                'supplier_id' => $supModels['SUP-005']->id,
                'supplier' => $supModels['SUP-005']->name,
                'cost_price' => 210000,
                'selling_price' => 285000,
                'stock' => 7,
                'min_stock' => 3,
                'unit' => 'Set',
                'location' => 'Rak C-2',
            ],
            [
                'part_number' => 'PRT-BRK-02',
                'name' => 'Brake Shoe Belakang Avanza / Xenia',
                'brand' => 'Bendix',
                'category' => 'Sistem Pengereman',
                'part_category_id' => $catModels['CAT-BRK']->id,
                'supplier_id' => $supModels['SUP-005']->id,
                'supplier' => $supModels['SUP-005']->name,
                'cost_price' => 170000,
                'selling_price' => 230000,
                'stock' => 0,
                'min_stock' => 2,
                'unit' => 'Set',
                'location' => 'Rak C-3',
            ], // OUT OF STOCK

            // --- MESIN (ENGINE PARTS) ---
            [
                'part_number' => 'PRT-SPK-01',
                'name' => 'Busi Iridium NGK Laser (Set 4 Pcs)',
                'brand' => 'NGK',
                'category' => 'Mesin (Engine Parts)',
                'part_category_id' => $catModels['CAT-ENG']->id,
                'supplier_id' => $supModels['SUP-001']->id,
                'supplier' => $supModels['SUP-001']->name,
                'cost_price' => 260000,
                'selling_price' => 350000,
                'stock' => 10,
                'min_stock' => 4,
                'unit' => 'Set',
                'location' => 'Rak D-1',
            ],
            [
                'part_number' => 'PRT-ENG-01',
                'name' => 'Paking Tutup Klep (Valve Cover Gasket) Avanza 1.3',
                'brand' => 'Toyota Genuine',
                'category' => 'Mesin (Engine Parts)',
                'part_category_id' => $catModels['CAT-ENG']->id,
                'supplier_id' => $supModels['SUP-001']->id,
                'supplier' => $supModels['SUP-001']->name,
                'cost_price' => 75000,
                'selling_price' => 110000,
                'stock' => 8,
                'min_stock' => 3,
                'unit' => 'Pcs',
                'location' => 'Rak D-2',
            ],
            [
                'part_number' => 'PRT-ENG-02',
                'name' => 'Fan Belt / Tali Kipas 6PK 1810 Avanza Dual VVT-i',
                'brand' => 'Bando',
                'category' => 'Mesin (Engine Parts)',
                'part_category_id' => $catModels['CAT-ENG']->id,
                'supplier_id' => $supModels['SUP-001']->id,
                'supplier' => $supModels['SUP-001']->name,
                'cost_price' => 115000,
                'selling_price' => 165000,
                'stock' => 9,
                'min_stock' => 3,
                'unit' => 'Pcs',
                'location' => 'Rak D-3',
            ],
            [
                'part_number' => 'PRT-ENG-03',
                'name' => 'Water Pump Radiator Avanza 1.3/1.5',
                'brand' => 'Aisin',
                'category' => 'Mesin (Engine Parts)',
                'part_category_id' => $catModels['CAT-ENG']->id,
                'supplier_id' => $supModels['SUP-001']->id,
                'supplier' => $supModels['SUP-001']->name,
                'cost_price' => 280000,
                'selling_price' => 390000,
                'stock' => 4,
                'min_stock' => 2,
                'unit' => 'Pcs',
                'location' => 'Rak D-4',
            ],
            [
                'part_number' => 'PRT-ENG-04',
                'name' => 'Engine Mounting Kanan Avanza / Xenia',
                'brand' => 'Toyota Genuine',
                'category' => 'Mesin (Engine Parts)',
                'part_category_id' => $catModels['CAT-ENG']->id,
                'supplier_id' => $supModels['SUP-001']->id,
                'supplier' => $supModels['SUP-001']->name,
                'cost_price' => 320000,
                'selling_price' => 450000,
                'stock' => 5,
                'min_stock' => 2,
                'unit' => 'Pcs',
                'location' => 'Rak D-5',
            ],

            // --- KAKI-KAKI & SUSPENSI (UNDERSTEEL) ---
            [
                'part_number' => 'PRT-SUS-01',
                'name' => 'Shockbreaker Depan Avanza / Xenia (Sepasang)',
                'brand' => 'Kayaba Excel-G',
                'category' => 'Kaki-Kaki & Suspensi',
                'part_category_id' => $catModels['CAT-SUS']->id,
                'supplier_id' => $supModels['SUP-002']->id,
                'supplier' => $supModels['SUP-002']->name,
                'cost_price' => 980000,
                'selling_price' => 1350000,
                'stock' => 4,
                'min_stock' => 2,
                'unit' => 'Set',
                'location' => 'Rak E-1',
            ],
            [
                'part_number' => 'PRT-SUS-02',
                'name' => 'Shockbreaker Belakang Avanza / Xenia (Sepasang)',
                'brand' => 'Kayaba Premium',
                'category' => 'Kaki-Kaki & Suspensi',
                'part_category_id' => $catModels['CAT-SUS']->id,
                'supplier_id' => $supModels['SUP-002']->id,
                'supplier' => $supModels['SUP-002']->name,
                'cost_price' => 520000,
                'selling_price' => 720000,
                'stock' => 6,
                'min_stock' => 2,
                'unit' => 'Set',
                'location' => 'Rak E-2',
            ],
            [
                'part_number' => 'PRT-SUS-03',
                'name' => 'Ball Joint Lower Depan Avanza (Sepasang)',
                'brand' => '555 Sankei Japan',
                'category' => 'Kaki-Kaki & Suspensi',
                'part_category_id' => $catModels['CAT-SUS']->id,
                'supplier_id' => $supModels['SUP-002']->id,
                'supplier' => $supModels['SUP-002']->name,
                'cost_price' => 290000,
                'selling_price' => 395000,
                'stock' => 8,
                'min_stock' => 3,
                'unit' => 'Set',
                'location' => 'Rak E-3',
            ],
            [
                'part_number' => 'PRT-SUS-04',
                'name' => 'Tie Rod End Luar Avanza / Rush (Sepasang)',
                'brand' => '555 Sankei Japan',
                'category' => 'Kaki-Kaki & Suspensi',
                'part_category_id' => $catModels['CAT-SUS']->id,
                'supplier_id' => $supModels['SUP-002']->id,
                'supplier' => $supModels['SUP-002']->name,
                'cost_price' => 240000,
                'selling_price' => 340000,
                'stock' => 7,
                'min_stock' => 3,
                'unit' => 'Set',
                'location' => 'Rak E-4',
            ],
            [
                'part_number' => 'PRT-SUS-05',
                'name' => 'Long Tie Rod / Rack End Avanza (Sepasang)',
                'brand' => '555 Sankei Japan',
                'category' => 'Kaki-Kaki & Suspensi',
                'part_category_id' => $catModels['CAT-SUS']->id,
                'supplier_id' => $supModels['SUP-002']->id,
                'supplier' => $supModels['SUP-002']->name,
                'cost_price' => 275000,
                'selling_price' => 380000,
                'stock' => 5,
                'min_stock' => 2,
                'unit' => 'Set',
                'location' => 'Rak E-5',
            ],
            [
                'part_number' => 'PRT-SUS-06',
                'name' => 'Link Stabilizer Depan Avanza / Xenia',
                'brand' => 'Toyota Genuine',
                'category' => 'Kaki-Kaki & Suspensi',
                'part_category_id' => $catModels['CAT-SUS']->id,
                'supplier_id' => $supModels['SUP-002']->id,
                'supplier' => $supModels['SUP-002']->name,
                'cost_price' => 140000,
                'selling_price' => 200000,
                'stock' => 10,
                'min_stock' => 4,
                'unit' => 'Set',
                'location' => 'Rak E-6',
            ],
            [
                'part_number' => 'PRT-SUS-07',
                'name' => 'Bushing Lower Arm Besar & Kecil Avanza',
                'brand' => 'RBI Thailand',
                'category' => 'Kaki-Kaki & Suspensi',
                'part_category_id' => $catModels['CAT-SUS']->id,
                'supplier_id' => $supModels['SUP-002']->id,
                'supplier' => $supModels['SUP-002']->name,
                'cost_price' => 165000,
                'selling_price' => 240000,
                'stock' => 12,
                'min_stock' => 4,
                'unit' => 'Set',
                'location' => 'Rak E-7',
            ],
            [
                'part_number' => 'PRT-SUS-08',
                'name' => 'Bearing Roda Depan Avanza (Dengan Sensor ABS)',
                'brand' => 'Koyo Japan',
                'category' => 'Kaki-Kaki & Suspensi',
                'part_category_id' => $catModels['CAT-SUS']->id,
                'supplier_id' => $supModels['SUP-002']->id,
                'supplier' => $supModels['SUP-002']->name,
                'cost_price' => 195000,
                'selling_price' => 285000,
                'stock' => 6,
                'min_stock' => 2,
                'unit' => 'Pcs',
                'location' => 'Rak E-8',
            ],

            // --- TRANSMISI & DRIVETRAIN ---
            [
                'part_number' => 'PRT-TRN-01',
                'name' => 'Kampas Kopling (Clutch Disc) Avanza 1.3',
                'brand' => 'Aisin',
                'category' => 'Transmisi & Drivetrain',
                'part_category_id' => $catModels['CAT-TRN']->id,
                'supplier_id' => $supModels['SUP-003']->id,
                'supplier' => $supModels['SUP-003']->name,
                'cost_price' => 360000,
                'selling_price' => 495000,
                'stock' => 5,
                'min_stock' => 2,
                'unit' => 'Pcs',
                'location' => 'Rak F-1',
            ],
            [
                'part_number' => 'PRT-TRN-02',
                'name' => 'Matahari Kopling / Dekrup (Clutch Cover) Avanza 1.3',
                'brand' => 'Aisin',
                'category' => 'Transmisi & Drivetrain',
                'part_category_id' => $catModels['CAT-TRN']->id,
                'supplier_id' => $supModels['SUP-003']->id,
                'supplier' => $supModels['SUP-003']->name,
                'cost_price' => 410000,
                'selling_price' => 565000,
                'stock' => 5,
                'min_stock' => 2,
                'unit' => 'Pcs',
                'location' => 'Rak F-2',
            ],
            [
                'part_number' => 'PRT-TRN-03',
                'name' => 'Release Bearing (Driyep) Kopling Avanza',
                'brand' => 'NSK Japan',
                'category' => 'Transmisi & Drivetrain',
                'part_category_id' => $catModels['CAT-TRN']->id,
                'supplier_id' => $supModels['SUP-003']->id,
                'supplier' => $supModels['SUP-003']->name,
                'cost_price' => 125000,
                'selling_price' => 180000,
                'stock' => 8,
                'min_stock' => 3,
                'unit' => 'Pcs',
                'location' => 'Rak F-3',
            ],
            [
                'part_number' => 'PRT-TRN-04',
                'name' => 'Master Kopling Atas (Clutch Master Cylinder) Avanza',
                'brand' => 'Sanyco Taiwan',
                'category' => 'Transmisi & Drivetrain',
                'part_category_id' => $catModels['CAT-TRN']->id,
                'supplier_id' => $supModels['SUP-003']->id,
                'supplier' => $supModels['SUP-003']->name,
                'cost_price' => 220000,
                'selling_price' => 310000,
                'stock' => 4,
                'min_stock' => 2,
                'unit' => 'Pcs',
                'location' => 'Rak F-4',
            ],
            [
                'part_number' => 'PRT-TRN-05',
                'name' => 'Master Kopling Bawah (Clutch Release Cylinder) Avanza',
                'brand' => 'Sanyco Taiwan',
                'category' => 'Transmisi & Drivetrain',
                'part_category_id' => $catModels['CAT-TRN']->id,
                'supplier_id' => $supModels['SUP-003']->id,
                'supplier' => $supModels['SUP-003']->name,
                'cost_price' => 145000,
                'selling_price' => 210000,
                'stock' => 5,
                'min_stock' => 2,
                'unit' => 'Pcs',
                'location' => 'Rak F-5',
            ],
            [
                'part_number' => 'PRT-TRN-06',
                'name' => 'Oli Transmisi Manual GL-4 75W-90 (1 Liter)',
                'brand' => 'Pertamina Rored',
                'category' => 'Transmisi & Drivetrain',
                'part_category_id' => $catModels['CAT-TRN']->id,
                'supplier_id' => $supModels['SUP-004']->id,
                'supplier' => $supModels['SUP-004']->name,
                'cost_price' => 60000,
                'selling_price' => 85000,
                'stock' => 15,
                'min_stock' => 4,
                'unit' => 'Botol',
                'location' => 'Rak F-6',
            ],
            [
                'part_number' => 'PRT-TRN-07',
                'name' => 'Oli Transmisi Matik ATF T-IV (4 Liter Galon)',
                'brand' => 'Toyota Genuine ATF',
                'category' => 'Transmisi & Drivetrain',
                'part_category_id' => $catModels['CAT-TRN']->id,
                'supplier_id' => $supModels['SUP-001']->id,
                'supplier' => $supModels['SUP-001']->name,
                'cost_price' => 340000,
                'selling_price' => 440000,
                'stock' => 7,
                'min_stock' => 2,
                'unit' => 'Galon',
                'location' => 'Rak F-7',
            ],
            [
                'part_number' => 'PRT-TRN-08',
                'name' => 'As Roda Luar / Outer CV Joint Avanza 1.3',
                'brand' => 'NKN Japan',
                'category' => 'Transmisi & Drivetrain',
                'part_category_id' => $catModels['CAT-TRN']->id,
                'supplier_id' => $supModels['SUP-003']->id,
                'supplier' => $supModels['SUP-003']->name,
                'cost_price' => 380000,
                'selling_price' => 520000,
                'stock' => 3,
                'min_stock' => 2,
                'unit' => 'Pcs',
                'location' => 'Rak F-8',
            ],
        ];

        $partModels = [];
        foreach ($parts as $prt) {
            $partModels[$prt['part_number']] = Part::create($prt);
        }

        // 5. Customers & Vehicles
        $cust1 = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081234567891',
            'email' => 'budi.santoso@gmail.com',
            'address' => 'Jl. Ketintang Baru No. 12, Surabaya',
            'notes' => 'Pelanggan setia, suka servis tepat waktu',
        ]);
        $veh1 = Vehicle::create([
            'customer_id' => $cust1->id,
            'plate_number' => 'L 1234 AB',
            'brand' => 'Toyota',
            'model' => 'Avanza 1.3 G AT',
            'year' => 2021,
            'transmission' => 'Automatic',
            'color' => 'Silver Metalik',
            'odometer' => 45200,
        ]);

        $cust2 = Customer::create([
            'name' => 'Cindy Claudia',
            'phone' => '081711223344',
            'email' => 'cindy.claudia@yahoo.com',
            'address' => 'Jl. Mayjend Sungkono No. 45, Surabaya',
        ]);
        $veh2 = Vehicle::create([
            'customer_id' => $cust2->id,
            'plate_number' => 'L 9081 EF',
            'brand' => 'Mitsubishi',
            'model' => 'Xpander Ultimate AT',
            'year' => 2020,
            'transmission' => 'Automatic',
            'color' => 'Putih Mutiara',
            'odometer' => 62100,
        ]);

        $cust3 = Customer::create([
            'name' => 'Anton Wijaya',
            'phone' => '081398765432',
            'email' => 'anton.wijaya@outlook.com',
            'address' => 'Jl. Rungkut Asri Timur No. 7, Surabaya',
        ]);
        $veh3 = Vehicle::create([
            'customer_id' => $cust3->id,
            'plate_number' => 'W 4567 CD',
            'brand' => 'Honda',
            'model' => 'Brio Satya E CVT',
            'year' => 2022,
            'transmission' => 'Automatic',
            'color' => 'Kuning Rally',
            'odometer' => 28500,
        ]);

        $cust4 = Customer::create([
            'name' => 'Rian Hidayat',
            'phone' => '085678901234',
            'email' => 'rian.hidayat@gmail.com',
            'address' => 'Jl. Manyar Kertoarjo No. 20, Surabaya',
        ]);
        $veh4 = Vehicle::create([
            'customer_id' => $cust4->id,
            'plate_number' => 'N 3344 GH',
            'brand' => 'Toyota',
            'model' => 'Innova Reborn 2.4 V AT',
            'year' => 2019,
            'transmission' => 'Automatic',
            'color' => 'Hitam Metalik',
            'odometer' => 89300,
        ]);

        $cust5 = Customer::create([
            'name' => 'Maya Indah',
            'phone' => '081987654321',
            'email' => 'maya.indah@gmail.com',
            'address' => 'Jl. Dharmahusada No. 15, Surabaya',
        ]);
        $veh5 = Vehicle::create([
            'customer_id' => $cust5->id,
            'plate_number' => 'L 7788 KL',
            'brand' => 'Daihatsu',
            'model' => 'Sigra 1.2 R MT',
            'year' => 2023,
            'transmission' => 'Manual',
            'color' => 'Merah Solid',
            'odometer' => 15400,
        ]);

        // 6. Work Orders:
        // WO 1: COMPLETED & PAID (Budi Santoso - Avanza)
        $wo1 = WorkOrder::create([
            'wo_number' => 'WO-2026-0001',
            'customer_id' => $cust1->id,
            'vehicle_id' => $veh1->id,
            'technician_id' => $tech1->id,
            'supplier_id' => $supModels['SUP-001']->id,
            'supplier_sales_id' => $salesModels[0]->id,
            'created_by' => $admin->id,
            'status' => 'COMPLETED',
            'complaint' => 'Servis berkala 45.000 KM dan ganti oli mesin.',
            'odometer_in' => 45200,
            'technician_notes' => 'Kondisi mesin bagus. Oli dan filter telah diganti. Rem sudah dibersihkan.',
            'qc_notes' => 'QC lolos, uji jalan tidak ada kendala suara/getaran.',
            'approval_token' => Str::random(40),
            'approval_status' => 'APPROVED',
            'approval_sent_at' => Carbon::now()->subDays(2)->setHour(9),
            'approved_at' => Carbon::now()->subDays(2)->setHour(10),
            'started_at' => Carbon::now()->subDays(2)->setHour(10)->addMinutes(15),
            'completed_at' => Carbon::now()->subDays(2)->setHour(14),
            'created_at' => Carbon::now()->subDays(2),
        ]);

        WorkOrderItem::create([
            'work_order_id' => $wo1->id,
            'type' => 'SERVICE',
            'service_id' => $svcModels['SRV-001']->id,
            'item_name' => 'Ganti Oli Mesin & Filter',
            'quantity' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
            'approval_status' => 'APPROVED',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo1->id,
            'type' => 'SERVICE',
            'service_id' => $svcModels['SRV-002']->id,
            'item_name' => 'Tune Up Mesin Injeksi',
            'quantity' => 1,
            'unit_price' => 250000,
            'subtotal' => 250000,
            'approval_status' => 'APPROVED',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo1->id,
            'type' => 'PART',
            'part_id' => $partModels['PRT-OIL-01']->id,
            'item_name' => 'Oli Castrol Magnatec 10W-40 (4L)',
            'quantity' => 1,
            'unit_price' => 385000,
            'subtotal' => 385000,
            'approval_status' => 'APPROVED',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo1->id,
            'type' => 'PART',
            'part_id' => $partModels['PRT-FLT-01']->id,
            'item_name' => 'Filter Oli Toyota Avanza',
            'quantity' => 1,
            'unit_price' => 48000,
            'subtotal' => 48000,
            'approval_status' => 'APPROVED',
        ]);
        $wo1->recalculateTotals();

        // Audit timeline WO 1
        $tEvents1 = [
            ['title' => 'WO Dibuat', 'desc' => 'Work order didaftarkan oleh Front Office', 'status' => 'DRAFT', 'time' => -48],
            ['title' => 'Teknisi Ditugaskan', 'desc' => 'Agus Pratama ditugaskan menangani unit', 'status' => 'INSPECTION', 'time' => -47],
            ['title' => 'Inspeksi Selesai', 'desc' => 'Pemeriksaan awal selesai & estimasi dibuat', 'status' => 'WAITING_APPROVAL', 'time' => -46],
            ['title' => 'Approval Dikirim', 'desc' => 'Estimasi biaya dikirimkan via WhatsApp pelanggan', 'status' => 'WAITING_APPROVAL', 'time' => -46],
            ['title' => 'Customer Approve', 'desc' => 'Pelanggan menyetujui estimasi melalui link digital', 'status' => 'APPROVED', 'time' => -45],
            ['title' => 'Pengerjaan Dimulai', 'desc' => 'Teknisi memulai perbaikan dan penggantian part', 'status' => 'IN_PROGRESS', 'time' => -44],
            ['title' => 'Quality Control (QC)', 'desc' => 'Pengecekan akhir pasca servis dan uji fungsi', 'status' => 'QC', 'time' => -42],
            ['title' => 'Siap Diambil', 'desc' => 'Kendaraan siap diambil & invoice diterbitkan', 'status' => 'READY_FOR_PICKUP', 'time' => -40],
            ['title' => 'Selesai & Diserahkan', 'desc' => 'Pembayaran lunas dan unit diserahkan ke pelanggan', 'status' => 'COMPLETED', 'time' => -38],
        ];
        foreach ($tEvents1 as $ev) {
            WoTimeline::create([
                'work_order_id' => $wo1->id,
                'user_id' => $admin->id,
                'title' => $ev['title'],
                'description' => $ev['desc'],
                'status' => $ev['status'],
                'created_at' => Carbon::now()->addHours($ev['time']),
            ]);
        }

        // Inspeksi WO 1
        $insp1 = Inspection::create([
            'work_order_id' => $wo1->id,
            'vehicle_id' => $veh1->id,
            'technician_id' => $tech1->id,
            'overall_summary' => 'Kondisi kendaraan secara umum prima, hanya perawatan periodik.',
            'status' => 'COMPLETED',
        ]);
        $inspItems1 = [
            ['cat' => 'ENGINE', 'name' => 'Oli Mesin', 'cond' => 'NEED_REPLACEMENT', 'notes' => 'Sudah 5.000 KM pemakaian'],
            ['cat' => 'ENGINE', 'name' => 'Air Radiator', 'cond' => 'GOOD', 'notes' => 'Level penuh dan jernih'],
            ['cat' => 'ENGINE', 'name' => 'Filter Udara', 'cond' => 'GOOD', 'notes' => 'Ditiup angin bersih'],
            ['cat' => 'BRAKE', 'name' => 'Kampas Rem Depan', 'cond' => 'GOOD', 'notes' => 'Tebal 7mm'],
            ['cat' => 'BRAKE', 'name' => 'Kampas Rem Belakang', 'cond' => 'GOOD', 'notes' => 'Tebal 5mm'],
            ['cat' => 'ELECTRICAL', 'name' => 'Aki / Battery', 'cond' => 'GOOD', 'notes' => 'Voltase 12.6V, CCA bagus'],
            ['cat' => 'ELECTRICAL', 'name' => 'Lampu Utama & Sein', 'cond' => 'GOOD', 'notes' => 'Semua menyala normal'],
        ];
        foreach ($inspItems1 as $it) {
            InspectionItem::create([
                'inspection_id' => $insp1->id,
                'category' => $it['cat'],
                'item_name' => $it['name'],
                'condition' => $it['cond'],
                'notes' => $it['notes'],
            ]);
        }

        // Invoice & Payment WO 1
        $inv1 = Invoice::create([
            'invoice_number' => 'INV-2026-0001',
            'work_order_id' => $wo1->id,
            'customer_id' => $cust1->id,
            'subtotal' => $wo1->subtotal,
            'discount' => 0,
            'tax' => $wo1->tax,
            'grand_total' => $wo1->grand_total,
            'amount_paid' => $wo1->grand_total,
            'balance_due' => 0,
            'payment_status' => 'PAID',
            'cashier_id' => $admin->id,
            'notes' => 'Pembayaran lunas kasir',
            'issued_at' => Carbon::now()->subDays(2)->setHour(14),
            'created_at' => Carbon::now()->subDays(2)->setHour(14),
        ]);

        Payment::create([
            'invoice_id' => $inv1->id,
            'payment_number' => 'PAY-2026-0001',
            'amount' => $inv1->grand_total,
            'payment_method' => 'CASH',
            'reference_number' => 'TUNAI-LUNAS',
            'cashier_id' => $admin->id,
            'paid_at' => Carbon::now()->subDays(2)->setHour(14)->addMinutes(10),
            'created_at' => Carbon::now()->subDays(2)->setHour(14)->addMinutes(10),
        ]);

        // ==========================================
        // WO 2: WAITING_APPROVAL (Cindy Claudia - Xpander)
        // ==========================================
        $tokenWo2 = 'tok_approval_' . Str::random(24);
        $wo2 = WorkOrder::create([
            'wo_number' => 'WO-2026-0002',
            'customer_id' => $cust2->id,
            'vehicle_id' => $veh2->id,
            'technician_id' => $tech2->id,
            'created_by' => $admin->id,
            'status' => 'WAITING_APPROVAL',
            'complaint' => 'AC tiba-tiba kurang dingin di siang hari & rem depan berdecit saat diinjak pelan.',
            'odometer_in' => 62100,
            'technician_notes' => 'Freon normal, evaporator kotor berdebu. Kampas rem depan sudah tipis tersisa 2mm.',
            'approval_token' => $tokenWo2,
            'approval_status' => 'PENDING',
            'approval_sent_at' => Carbon::now()->subHours(3),
            'approval_expires_at' => Carbon::now()->addHours(21),
            'created_at' => Carbon::now()->subHours(4),
        ]);

        WorkOrderItem::create([
            'work_order_id' => $wo2->id,
            'type' => 'SERVICE',
            'service_id' => $svcModels['SRV-004']->id,
            'item_name' => 'Servis AC Lengkap + Fogging',
            'quantity' => 1,
            'unit_price' => 450000,
            'subtotal' => 450000,
            'approval_status' => 'PENDING',
            'notes' => 'Pembersihan blower & evaporator',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo2->id,
            'type' => 'SERVICE',
            'service_id' => $svcModels['SRV-003']->id,
            'item_name' => 'Overhaul & Servis Rem 4 Roda',
            'quantity' => 1,
            'unit_price' => 200000,
            'subtotal' => 200000,
            'approval_status' => 'PENDING',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo2->id,
            'type' => 'PART',
            'part_id' => $partModels['PRT-BRK-01']->id,
            'item_name' => 'Brake Pad Depan Toyota/Mitsubishi',
            'quantity' => 1,
            'unit_price' => 285000,
            'subtotal' => 285000,
            'approval_status' => 'PENDING',
        ]);
        $wo2->recalculateTotals();

        // Inspeksi WO 2
        $insp2 = Inspection::create([
            'work_order_id' => $wo2->id,
            'vehicle_id' => $veh2->id,
            'technician_id' => $tech2->id,
            'overall_summary' => 'Evaporator kotor dan kampas rem depan tipis. Perlu segera persetujuan pelanggan.',
            'status' => 'COMPLETED',
        ]);
        $inspItems2 = [
            ['cat' => 'BRAKE', 'name' => 'Kampas Rem Depan', 'cond' => 'NEED_REPLACEMENT', 'notes' => 'Sisa 2mm, pelat indikator mulai menggesek piringan'],
            ['cat' => 'BRAKE', 'name' => 'Kampas Rem Belakang', 'cond' => 'GOOD', 'notes' => 'Masih 6mm'],
            ['cat' => 'ENGINE', 'name' => 'Blower & Evaporator AC', 'cond' => 'BAD', 'notes' => 'Blower tertutup debu tebal, bau apek'],
            ['cat' => 'ENGINE', 'name' => 'Tekanan Freon AC', 'cond' => 'GOOD', 'notes' => 'Low 30 psi, High 210 psi'],
            ['cat' => 'ELECTRICAL', 'name' => 'Motor Blower Fan', 'cond' => 'GOOD', 'notes' => 'Putaran kencang normal'],
        ];
        foreach ($inspItems2 as $it) {
            InspectionItem::create([
                'inspection_id' => $insp2->id,
                'category' => $it['cat'],
                'item_name' => $it['name'],
                'condition' => $it['cond'],
                'notes' => $it['notes'],
            ]);
        }

        // Timeline WO 2
        WoTimeline::create([
            'work_order_id' => $wo2->id,
            'user_id' => $admin->id,
            'title' => 'WO Dibuat',
            'description' => 'Keluhan AC & rem dicatat oleh kasir',
            'status' => 'DRAFT',
            'created_at' => Carbon::now()->subHours(4),
        ]);
        WoTimeline::create([
            'work_order_id' => $wo2->id,
            'user_id' => $tech2->id,
            'title' => 'Inspeksi & Diagnosa Selesai',
            'description' => 'Teknisi Doni menemukan evaporator kotor dan kampas rem depan aus',
            'status' => 'INSPECTION',
            'created_at' => Carbon::now()->subHours(3)->subMinutes(30),
        ]);
        WoTimeline::create([
            'work_order_id' => $wo2->id,
            'user_id' => $admin->id,
            'title' => 'Estimasi Dikirim via WhatsApp',
            'description' => 'Tautan persetujuan customer approval dikirim ke nomor 081711223344',
            'status' => 'WAITING_APPROVAL',
            'created_at' => Carbon::now()->subHours(3),
        ]);

        WhatsappLog::create([
            'work_order_id' => $wo2->id,
            'customer_name' => $cust2->name,
            'phone' => $cust2->phone,
            'message' => "Halo Kak Cindy Claudia, estimasi perbaikan Mitsubishi Xpander (L 9081 EF) telah selesai. Total estimasi: Rp " . number_format($wo2->grand_total, 0, ',', '.') . ". Silakan tinjau dan setujui perbaikan melalui tautan berikut: http://localhost:8080/approval/{$tokenWo2}",
            'status' => 'SENT',
            'created_at' => Carbon::now()->subHours(3),
        ]);

        // ==========================================
        // WO 3: IN_PROGRESS (Anton Wijaya - Brio)
        // ==========================================
        $wo3 = WorkOrder::create([
            'wo_number' => 'WO-2026-0003',
            'customer_id' => $cust3->id,
            'vehicle_id' => $veh3->id,
            'technician_id' => $tech1->id,
            'supplier_id' => $supModels['SUP-002']->id,
            'supplier_sales_id' => $salesModels[4]->id,
            'created_by' => $admin->id,
            'status' => 'IN_PROGRESS',
            'complaint' => 'Getaran mesin terasa kasar saat AC menyala di lampu merah. Ganti oli sekalian.',
            'odometer_in' => 28500,
            'technician_notes' => 'Pembersihan throttle body dan kalibrasi idle. Ganti oli mesin Shell.',
            'approval_token' => Str::random(40),
            'approval_status' => 'APPROVED',
            'approval_sent_at' => Carbon::now()->subHours(2),
            'approved_at' => Carbon::now()->subHours(1)->subMinutes(40),
            'started_at' => Carbon::now()->subHours(1)->subMinutes(30),
            'created_at' => Carbon::now()->subHours(3),
        ]);

        WorkOrderItem::create([
            'work_order_id' => $wo3->id,
            'type' => 'SERVICE',
            'service_id' => $svcModels['SRV-007']->id,
            'item_name' => 'Pembersihan Throttle Body & Intake',
            'quantity' => 1,
            'unit_price' => 120000,
            'subtotal' => 120000,
            'approval_status' => 'APPROVED',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo3->id,
            'type' => 'SERVICE',
            'service_id' => $svcModels['SRV-001']->id,
            'item_name' => 'Ganti Oli Mesin & Filter',
            'quantity' => 1,
            'unit_price' => 75000,
            'subtotal' => 75000,
            'approval_status' => 'APPROVED',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo3->id,
            'type' => 'PART',
            'part_id' => $partModels['PRT-OIL-02']->id,
            'item_name' => 'Oli Shell Helix Ultra 5W-30 Full Syn (4L)',
            'quantity' => 1,
            'unit_price' => 560000,
            'subtotal' => 560000,
            'approval_status' => 'APPROVED',
        ]);
        $wo3->recalculateTotals();

        WoTimeline::create([
            'work_order_id' => $wo3->id,
            'user_id' => $admin->id,
            'title' => 'WO Dibuat',
            'description' => 'Keluhan mesin getar didaftarkan',
            'status' => 'DRAFT',
            'created_at' => Carbon::now()->subHours(3),
        ]);
        WoTimeline::create([
            'work_order_id' => $wo3->id,
            'user_id' => $admin->id,
            'title' => 'Customer Menyetujui Estimasi',
            'description' => 'Disetujui via WhatsApp',
            'status' => 'APPROVED',
            'created_at' => Carbon::now()->subHours(1)->subMinutes(40),
        ]);
        WoTimeline::create([
            'work_order_id' => $wo3->id,
            'user_id' => $tech1->id,
            'title' => 'Pengerjaan Dimulai',
            'description' => 'Teknisi Agus Pratama membongkar throttle body dan kuras oli',
            'status' => 'IN_PROGRESS',
            'created_at' => Carbon::now()->subHours(1)->subMinutes(30),
        ]);

        // ==========================================
        // WO 4: READY_FOR_PICKUP (Rian Hidayat - Innova)
        // ==========================================
        $wo4 = WorkOrder::create([
            'wo_number' => 'WO-2026-0004',
            'customer_id' => $cust4->id,
            'vehicle_id' => $veh4->id,
            'technician_id' => $tech3->id,
            'created_by' => $admin->id,
            'status' => 'READY_FOR_PICKUP',
            'complaint' => 'Tarikan agak berat dan temperatur sedikit naik saat macet panjang.',
            'odometer_in' => 89300,
            'technician_notes' => 'Flushing radiator coolant Prestone dan ganti busi iridium. Uji fungsi normal.',
            'qc_notes' => 'QC OK, temperatur stabil di 88 derajat celcius.',
            'approval_token' => Str::random(40),
            'approval_status' => 'APPROVED',
            'approval_sent_at' => Carbon::now()->subHours(6),
            'approved_at' => Carbon::now()->subHours(5)->subMinutes(30),
            'started_at' => Carbon::now()->subHours(5),
            'completed_at' => Carbon::now()->subHour(),
            'created_at' => Carbon::now()->subHours(7),
        ]);

        WorkOrderItem::create([
            'work_order_id' => $wo4->id,
            'type' => 'SERVICE',
            'service_id' => $svcModels['SRV-006']->id,
            'item_name' => 'Flushing Radiator Coolant',
            'quantity' => 1,
            'unit_price' => 90000,
            'subtotal' => 90000,
            'approval_status' => 'APPROVED',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo4->id,
            'type' => 'SERVICE',
            'service_id' => $svcModels['SRV-002']->id,
            'item_name' => 'Tune Up Mesin Injeksi',
            'quantity' => 1,
            'unit_price' => 250000,
            'subtotal' => 250000,
            'approval_status' => 'APPROVED',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo4->id,
            'type' => 'PART',
            'part_id' => $partModels['PRT-CLT-01']->id,
            'item_name' => 'Air Radiator Coolant Prestone 4L',
            'quantity' => 2,
            'unit_price' => 120000,
            'subtotal' => 240000,
            'approval_status' => 'APPROVED',
        ]);
        WorkOrderItem::create([
            'work_order_id' => $wo4->id,
            'type' => 'PART',
            'part_id' => $partModels['PRT-SPK-01']->id,
            'item_name' => 'Busi Iridium NGK Laser (Set 4 Pcs)',
            'quantity' => 1,
            'unit_price' => 350000,
            'subtotal' => 350000,
            'approval_status' => 'APPROVED',
        ]);
        $wo4->recalculateTotals();

        // Invoice WO 4 (Siap dibayar di kasir)
        $inv4 = Invoice::create([
            'invoice_number' => 'INV-2026-0002',
            'work_order_id' => $wo4->id,
            'customer_id' => $cust4->id,
            'subtotal' => $wo4->subtotal,
            'discount' => 50000, // Diskon member
            'tax' => round(($wo4->subtotal - 50000) * 0.11, 2),
            'grand_total' => ($wo4->subtotal - 50000) + round(($wo4->subtotal - 50000) * 0.11, 2),
            'amount_paid' => 0,
            'balance_due' => ($wo4->subtotal - 50000) + round(($wo4->subtotal - 50000) * 0.11, 2),
            'payment_status' => 'UNPAID',
            'cashier_id' => $admin->id,
            'notes' => 'Menunggu pengambilan unit oleh customer',
            'issued_at' => Carbon::now()->subHour(),
            'created_at' => Carbon::now()->subHour(),
        ]);

        WoTimeline::create([
            'work_order_id' => $wo4->id,
            'user_id' => $admin->id,
            'title' => 'Unit Selesai & Lolos QC',
            'description' => 'Kendaraan telah diparkir di area serah terima, customer dikabari via WhatsApp',
            'status' => 'READY_FOR_PICKUP',
            'created_at' => Carbon::now()->subHour(),
        ]);

        // ==========================================
        // WO 5: INSPECTION (Maya Indah - Sigra)
        // ==========================================
        $wo5 = WorkOrder::create([
            'wo_number' => 'WO-2026-0005',
            'customer_id' => $cust5->id,
            'vehicle_id' => $veh5->id,
            'technician_id' => $tech3->id,
            'created_by' => $admin->id,
            'status' => 'INSPECTION',
            'complaint' => 'Roda depan berbunyi gluduk-gluduk saat melewati jalan bergelombang.',
            'odometer_in' => 15400,
            'created_at' => Carbon::now()->subMinutes(50),
        ]);

        $insp5 = Inspection::create([
            'work_order_id' => $wo5->id,
            'vehicle_id' => $veh5->id,
            'technician_id' => $tech3->id,
            'overall_summary' => 'Sedang dalam pengecekan tierod dan link stabilizer.',
            'status' => 'IN_PROGRESS',
        ]);
        InspectionItem::create([
            'inspection_id' => $insp5->id,
            'category' => 'SUSPENSION',
            'item_name' => 'Link Stabilizer Kanan & Kiri',
            'condition' => 'BAD',
            'notes' => 'Karet robek dan balljoint oblak',
        ]);
        InspectionItem::create([
            'inspection_id' => $insp5->id,
            'category' => 'SUSPENSION',
            'item_name' => 'Bushing Arm Depan',
            'condition' => 'WARNING',
            'notes' => 'Mulai retak rambut',
        ]);
        InspectionItem::create([
            'inspection_id' => $insp5->id,
            'category' => 'BRAKE',
            'item_name' => 'Kampas Rem Depan',
            'condition' => 'GOOD',
            'notes' => 'Tebal 8mm',
        ]);

        WoTimeline::create([
            'work_order_id' => $wo5->id,
            'user_id' => $admin->id,
            'title' => 'WO Dibuat',
            'description' => 'Keluhan kaki-kaki dicatat',
            'status' => 'DRAFT',
            'created_at' => Carbon::now()->subMinutes(50),
        ]);
        WoTimeline::create([
            'work_order_id' => $wo5->id,
            'user_id' => $tech3->id,
            'title' => 'Inspeksi Dimulai',
            'description' => 'Hendra Setiawan menaikkan mobil ke car lift untuk cek kaki-kaki',
            'status' => 'INSPECTION',
            'created_at' => Carbon::now()->subMinutes(30),
        ]);
    }
}
