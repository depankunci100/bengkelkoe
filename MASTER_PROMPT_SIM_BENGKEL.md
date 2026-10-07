# MASTER PROMPT — SIM BENGKEL (SISTEM INFORMASI MANAJEMEN BENGKEL)
## MODUL: BOOTSTRAP 5 WEB & HYBRID ARCHITECTURE

Dokumen ini merupakan panduan arsitektur, standar UI/UX, dan spesifikasi fungsional untuk pengembangan **SIM BENGKEL Web Application** dan integrasinya dengan REST API / Flutter Mobile.

---

### 1. RINGKASAN ARSITEKTUR & STACK TEKNOLOGI

```text
                         CUSTOMER
                            │
                            │
                         WHATSAPP
                            │
                            ▼
                  CUSTOMER APPROVAL (/approval/{token})
                            │
                            ▼
┌────────────────────────────────────────────────────────┐
│                      LARAVEL 13                        │
│                                                        │
│ ┌──────────────────────┐     ┌───────────────────────┐ │
│ │   WEB APPLICATION    │     │       REST API        │ │
│ │                      │     │                       │ │
│ │ Blade Components     │     │ /api/v1/*             │ │
│ │ Bootstrap 5 + Icons  │     │ Laravel Sanctum       │ │
│ │ Alpine.js / Fetch API│     │                       │ │
│ │ Chart.js             │     │                       │ │
│ └──────────┬───────────┘     └───────────┬───────────┘ │
│            │                             │             │
│            └──────────────┬──────────────┘             │
│                           │                            │
│                    BUSINESS LOGIC                      │
│                           │                            │
│       ┌───────────────────┼───────────────────┐        │
│       │                   │                   │        │
│   Work Order         Inventory             Payment     │
│   Inspection         WhatsApp Service      Invoice     │
│       │                   │                   │        │
└───────┼───────────────────┼───────────────────┼────────┘
        │                   │                   │
        ▼                   ▼                   ▼
      MySQL               Redis              Storage
        │               (Queue/Cache)     (Foto, PDF, S3)
        ▼
     Flutter
   (Android/iOS)
```

#### Prinsip Utama:
1. **Laravel 13**: Menjadi *single source of truth* untuk seluruh data dan *business logic*. Tidak ada duplikasi aturan bisnis antara Web dan Mobile.
2. **Web Application (Blade + Bootstrap 5 + Icons + Vanilla JS/Alpine.js)**: Digunakan oleh **Owner**, **Admin/Kasir**, dan **Teknisi**. *Bukan SPA (React/Vue/Angular)*.
3. **REST API (`/api/v1`)**: Melayani aplikasi Flutter (Android & iOS) dengan autentikasi Laravel Sanctum.
4. **WhatsApp Service**: Gateway notifikasi, pengiriman rincian estimasi, dan link token approval mandiri ke pelanggan.
5. **Customer Approval Web (`/approval/{token}`)**: Halaman web publik tanpa login admin, responsif dan ringan, khusus untuk persetujuan/penolakan estimasi pekerjaan oleh customer.

---

### 2. CORE SPECIFICATION & UI/UX RULES

#### A. Bootstrap 5 Standard
- Framework UI utama: **Bootstrap 5** + **Bootstrap Icons (`bi bi-*`)**.
- Aset dikompilasi melalui **Vite** (`resources/scss/app.scss` & `resources/js/app.js`).
- Hindari CSS kustom berlebih; maksimalkan Bootstrap utilities (`d-flex`, `gap-*`, `shadow-sm`, `border-0`, `rounded-*`).
- Kustom CSS hanya untuk: branding warna bengkel (`primary_color`), layout sidebar/offcanvas khusus, status badge, print invoice.

#### B. Layout Web Admin
- **Header Top**: Logo bengkel, quick search, notifikasi realtime badge, profile dropdown.
- **Responsive Sidebar**:
  - Desktop/Laptop: Sidebar fixed/collapsible.
  - Tablet/Mobile (`< 992px`): Berganti otomatis menjadi **Bootstrap Offcanvas**.
- **Responsive Breakpoints**: Mendukung `xs`, `sm`, `md`, `lg`, `xl`, `xxl`.
- Data tabel menggunakan wrapper `.table-responsive` dan modal dialog `.modal-dialog-scrollable`.

#### C. Reusable Blade Components (`resources/views/components/`)
- `x-button`: Button varian dengan loading state dan ikon.
- `x-card`: Container card standar berbayang halus (`shadow-sm border-0`).
- `x-modal`: Modal dialog konfirmasi/form dengan trigger Bootstrap 5.
- `x-status-badge`: Badge status dinamis sesuai mapping status WO/Approval.
- `x-form.input`, `x-form.select`, `x-form.textarea`: Form kontrol standar.
- `x-empty-state`: Tampilan data kosong yang rapi dengan ikon & tombol aksi.
- `x-loading`: Spinner indikator saat AJAX/Fetch berjalan.

---

### 3. ROLE & MENU ACCESS MATRIX

| Modul / Menu | Owner | Admin / Kasir | Teknisi | Customer (Public Token) |
| :--- | :---: | :---: | :---: | :---: |
| **Dashboard Metrik & Chart** | ✅ Penuh (Revenue, Profit, WO) | ✅ Terbatas (WO, Siap Diambil) | ✅ WO & Tugas Saya | ❌ |
| **Customer & Kendaraan** | ✅ Full CRUD | ✅ Full CRUD | 👁️ Read Only | ❌ |
| **Work Order (WO)** | ✅ Full CRUD & Override | ✅ Buat, Edit, Cetak | 👁️ Kerjakan & Update Status | ❌ |
| **Inspeksi (Accordion Check)** | ✅ Review | 👁️ Read Only | ✅ Input, Foto & Catatan | ❌ |
| **Approval Dashboard** | ✅ Monitor & Override | ✅ Monitor & Kirim WA | 👁️ Read Only | ❌ |
| **Approval Customer Page** | ❌ | ❌ | ❌ | ✅ `/approval/{token}` |
| **Inventory / Sparepart** | ✅ Full CRUD & Stok Opname | 👁️ Cek Stok & Jual | 👁️ Request Part WO | ❌ |
| **Pembayaran & Kasir** | ✅ Full Monitor & Void | ✅ Proses Kasir & Split Pay | ❌ | ❌ |
| **Invoice & Cetak Print** | ✅ Full Access | ✅ Cetak & Kirim | ❌ | 👁️ Lihat PDF |
| **Laporan & Export (Excel/PDF)**| ✅ Full Laporan Keuangan | 👁️ Rekap Kasir Harian | ❌ | ❌ |
| **Pengaturan Bengkel & User** | ✅ Full Konfigurasi | ❌ | ❌ | ❌ |

---

### 4. WORK ORDER (WO) STATUS LIFECYCLE & BADGE MAPPING

```text
[DRAFT] ──> [INSPECTION] ──> [WAITING_APPROVAL] ──> [APPROVED] ──> [IN_PROGRESS] ──> [QC] ──> [READY_FOR_PICKUP] ──> [COMPLETED]
                                   │
                                   ├──> [REJECTED]
                                   └──> [CANCELLED]
```

| Status Enum | Badge Bootstrap 5 | Keterangan |
| :--- | :--- | :--- |
| `DRAFT` | `<span class="badge bg-secondary">Draft</span>` | WO baru dibuat, belum diinspeksi |
| `INSPECTION` | `<span class="badge bg-info text-dark">Inspeksi</span>` | Teknisi sedang memeriksa kendaraan |
| `WAITING_APPROVAL` | `<span class="badge bg-warning text-dark">Menunggu Approval</span>` | Estimasi dikirim ke WhatsApp customer |
| `APPROVED` | `<span class="badge bg-primary">Disetujui</span>` | Customer menyetujui seluruh/sebagian estimasi |
| `IN_PROGRESS` | `<span class="badge bg-primary">Dikerjakan</span>` | Teknisi mulai melakukan perbaikan |
| `PAUSED` | `<span class="badge bg-secondary">Ditunda</span>` | Menunggu part / persetujuan tambahan |
| `QC` | `<span class="badge bg-info text-dark">Quality Control</span>` | Pengecekan akhir pasca servis |
| `READY_FOR_PICKUP` | `<span class="badge bg-success">Siap Diambil</span>` | Servis selesai, customer diinfokan |
| `COMPLETED` | `<span class="badge bg-success">Selesai</span>` | Kendaraan diserahkan & pembayaran lunas |
| `REJECTED` | `<span class="badge bg-danger">Ditolak</span>` | Customer menolak estimasi pengerjaan |
| `CANCELLED` | `<span class="badge bg-dark">Dibatalkan</span>` | WO dibatalkan |

---

### 5. FITUR UTAMA & SPESIFIKASI HALAMAN

1. **Dashboard Eksekutif & Chart.js**:
   - Card ringkasan: WO Hari Ini (trend %), Pendapatan Realtime, Antrean Approval, Unit Siap Diambil.
   - Grafik Pendapatan: 7 hari terakhir, 30 hari, dan bulan berjalan.
   - Grafik Distribusi Status WO (Pie/Donut).
   - Top 5 Jasa Terlaris & Top 10 Sparepart Paling Sering Digunakan.

2. **Work Order Detail & Audit Timeline**:
   - Detail informasi Customer, Kendaraan, Keluhan awal, dan Teknisi penanggung jawab.
   - Live Timeline pengerjaan berbasis log audit riil (`wo_timelines` / `audit_logs`).
   - Rincian biaya: Jasa, Sparepart, Subtotal, Diskon, Pajak (PPN), Grand Total.
   - Tombol Aksi: Preview & Kirim WhatsApp Approval Modal.

3. **Digital Inspection Checklist**:
   - Accordion per kategori: *ENGINE*, *BRAKE*, *SUSPENSION*, *ELECTRICAL*, *BODY/INTERIOR*.
   - Status per item: `GOOD`, `WARNING`, `BAD`, `NEED REPLACEMENT`.
   - Unggah foto pendukung & catatan teknisi secara instan via Fetch API / AJAX.

4. **Approval & Partial Approval Flow**:
   - Customer menerima tautan unik WhatsApp: `/approval/{token}`.
   - Customer dapat memilih item mana yang disetujui atau ditolak secara parsial (itemized approval).
   - Validasi token kedaluwarsa (*expiry timestamp*).
   - Notifikasi balik ke dashboard kasir saat customer merespons persetujuan.

5. **Point of Sale (POS) & Kasir Pembayaran**:
   - Antarmuka pembayaran cepat satu layar: input nominal, sisa tagihan, metode pembayaran (Cash, Transfer Bank, QRIS, Debit/Kredit).
   - Dukungan pembayaran bertahap / down payment (DP) dan pelunasan saat pengambilan unit.

6. **Invoice & Print-Friendly Layout**:
   - Route cetak: `/invoices/{invoice}/print`.
   - Desain terisolasi (`resources/views/layouts/print.blade.php`) dengan `@media print { .no-print { display: none !important; } }`.
   - Layout rapi untuk printer thermal kasir (58mm/80mm) dan kertas formal A4/Letter.

7. **Inventory & Smart Stock Alert**:
   - Peringatan stok minimum: Badge `<span class="badge bg-warning text-dark">Stok Rendah</span>` & `<span class="badge bg-danger">Habis</span>`.
   - Riwayat mutasi stok (Pembelian, Penggunaan WO, Retur, Penyesuaian/Stock Opname).

---

### 6. PANDUAN PENGEMBANGAN TEKNIS

- **Pagination**: Selalu gunakan server-side pagination (`$data->withQueryString()->links()`) untuk tabel besar.
- **Konfirmasi Modal**: Setiap tindakan kritis (Delete, Cancel WO, Void Payment, Stock Adjustment) wajib menggunakan Bootstrap Confirmation Modal (`data-bs-toggle="modal"`), dilarang menggunakan default browser alert.
- **Flash Message**: Gunakan Alert dismissible Bootstrap 5 untuk umpan balik controller (`session('success')`, `session('error')`).
- **Keamanan**: Seluruh route web dilindungi middleware `auth` dan Policy otorisasi `@can` untuk memverifikasi role & permission.
