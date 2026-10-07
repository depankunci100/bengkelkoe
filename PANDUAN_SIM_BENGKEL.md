# PANDUAN PENGGUNAAN & PENGUJIAN SIM BENGKEL
## Laravel 13 + Bootstrap 5.3 Web Application & REST API

Sistem Informasi Manajemen Bengkel (**SIM BENGKEL**) telah selesai diimplementasikan secara komprehensif mengikuti seluruh 41 poin spesifikasi pada **Master Prompt**.

---

### 1. CARA MENJALANKAN APLIKASI

Buka terminal pada Mac dan jalankan script otomatis:

```bash
cd "/Users/ameliavirismanda/Documents/Mas Huda/sim_bengkel"
./start.sh
```

Atau menggunakan PHP Herd secara manual:
```bash
cd "/Users/ameliavirismanda/Documents/Mas Huda/sim_bengkel"
"/Users/ameliavirismanda/Library/Application Support/Herd/bin/php" artisan serve --port=8080
```

- **Akses Web Komputer/Mac**: [http://127.0.0.1:8080](http://127.0.0.1:8080)
- **Akses HP / Tablet (WiFi yang sama)**: `http://<IP_MAC_ANDA>:8080`

---

### 2. AKUN DEMO & 1-KLIK LOGIN CEPAT

Pada halaman login ([http://127.0.0.1:8080/login](http://127.0.0.1:8080/login)), tersedia tombol **1-Klik Demo Login** instan untuk 3 peran:

| Peran (Role) | Akun Email | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Owner** (Bambang Wijaya) | `owner@bengkel.com` | `password` | **Akses Penuh**: Omset, Keuangan, Master Data, Laporan Analitik, Pengaturan & Tema |
| **Admin / Kasir** (Siti Rahma) | `admin@bengkel.com` | `password` | **Operasional & Transaksi**: Pendaftaran Customer, Work Order, Kasir POS, Cetak Faktur |
| **Teknisi** (Agus Pratama) | `teknisi@bengkel.com` | `password` | **Pengerjaan & Diagnosa**: Checklist Inspeksi, Eksekusi WO, Pengajuan Part Tambahan, QC |

---

### 3. FITUR & MODUL UTAMA YANG TELAH TERIMPLEMENTASI

#### A. Dashboard Utama (Cards & Chart.js)
- **KPI Cards**: *WO Hari Ini*, *Pendapatan Realtime*, *Menunggu Approval*, *Siap Diambil*.
- **Grafik Interaktif**:
  - Tren Pendapatan 7 Hari Terakhir (Line Chart).
  - Distribusi Siklus Status Work Order (Doughnut Chart).
- **Widgets Operasional**: Jasa Paling Sering Dikerjakan & Peringatan Stok Suku Cadang Kritis (*Low Stock Alert*).

#### B. Customer & Kendaraan
- **Customer List**: Filter pencarian cepat, kontak WhatsApp, dan server-side pagination.
- **Customer Detail**: Tampilan tabbed (*Informasi*, *Kendaraan*, *Riwayat Servis*, *Faktur Invoice*).
- **Kendaraan**: Registrasi unit, nomor plat, merk, model, transmisi, dan tracking odometer.

#### C. Work Order (SPK Servis) & Alur Lifecycle
- Layout utama sesuai Master Prompt Section 11 (Header, Status Badge, Customer, Kendaraan, Keluhan, Teknisi, Hasil Inspeksi, Estimasi, Aksi WhatsApp).
- **Audit Timeline Riil**: Pencatatan riwayat setiap event pengerjaan (*WO Dibuat &rarr; Teknisi Ditugaskan &rarr; Inspeksi Selesai &rarr; Approval Dikirim &rarr; Disetujui &rarr; Pengerjaan &rarr; QC &rarr; Siap Diambil &rarr; Selesai*).
- **Pekerjaan Tambahan**: Fitur teknisi mengajukan part/jasa tambahan saat menemukan kerusakan tak terduga.

#### D. Lembar Inspeksi Digital (Checklist Accordion)
- Pengelompokan accordion kategori: `ENGINE`, `BRAKE`, `ELECTRICAL`, `SUSPENSION`, `BODY & INTERIOR`.
- Penilaian 4 status kondisi: `GOOD`, `WARNING`, `BAD`, `NEED REPLACEMENT` dengan catatan teknis.

#### E. Portal Persetujuan Pelanggan Mandiri (`/approval/{token}`)
- Halaman web publik khusus tanpa login admin.
- Dikirimkan via link WhatsApp ke pelanggan.
- Pelanggan dapat memilih:
  - **Setujui Seluruh Perbaikan** (`btn-success`).
  - **Persetujuan Parsial** (Memilih item jasa/part tertentu yang ingin disetujui atau ditunda).
  - **Tolak Perbaikan** (`btn-danger`).

#### F. Gudang & Suku Cadang (Inventory)
- Master sparepart, harga beli modal, harga jual, dan lokasi rak.
- Peringatan stok otomatis: `<span class="badge bg-warning text-dark">Stock Rendah</span>` & `<span class="badge bg-danger">Habis</span>`.
- **Riwayat Mutasi Stok**: Tracking barang masuk (*IN*), barang keluar pengerjaan WO (*OUT*), dan *Stock Opname / Adjustment*.

#### G. Kasir Pembayaran (POS) & Cetak Faktur (Print-Friendly)
- **Kasir POS 1 Layar**: Input nominal pembayaran tunai (*Cash*), transfer bank, QRIS, atau kartu EDC.
- **Faktur Cetak**: Halaman terisolasi (`resources/views/layouts/print.blade.php`) dengan styling `@media print` untuk mencetak invoice resmi A4 dan struk kasir.

#### H. Laporan Kinerja & Pengaturan Tema
- Rekapitulasi omset per rentang tanggal, produktivitas per teknisi, dan status pengerjaan.
- Tombol ekspor & cetak laporan.
- Konfigurasi identitas bengkel dan kustomisasi warna tema primer Bootstrap 5.

---

### 4. REST API UNTUK FLUTTER MOBILE (`/api/v1`)

Backend siap berkomunikasi dengan aplikasi mobile Flutter Android & iOS:

- `GET /api/v1/work-orders` - Mengambil daftar work order
- `GET /api/v1/work-orders/{id}` - Detail work order beserta inspeksi dan rincian item
- `POST /api/v1/work-orders/{id}/status` - Update status pengerjaan oleh teknisi mobile
- `GET /api/v1/parts` - Pencarian katalog suku cadang & ketersediaan stok
