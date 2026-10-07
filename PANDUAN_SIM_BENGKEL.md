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
- Master sparepart dengan kategori spesifik: **Kaki-Kaki / Suspensi**, **Mesin & Internal Engine**, **Transmisi & Drivetrain**, **Pengereman**, **Kelistrikan**, dsb.
- **Manajemen Batch & Beda Harga Beli**: Setiap penerimaan barang baru dapat mencatat harga modal yang berbeda tanpa mengubah riwayat batch sebelumnya.
- Peringatan stok otomatis: `<span class="badge bg-warning text-dark">Stock Rendah</span>` & `<span class="badge bg-danger">Habis</span>`.
- **Riwayat Mutasi Stok**: Tracking barang masuk (*IN*), barang keluar pengerjaan WO (*OUT*), dan *Stock Opname / Adjustment*.

#### G. Master Supplier & Multi-Sales Representative (Penerimaan Barang Bengkel)
- **1 Supplier & Multi Sales**: Satu perusahaan supplier (misal: PT Astra Otoparts, CV Sumber Makmur) dapat memiliki beberapa sales person aktif. Setiap sales dicatat nama, no HP/WhatsApp, dan catatan spesialisasi barangnya.
- **Pencatatan Sales PIC saat Barang Masuk (Stock Opname / WO Supplier)**:
  - Saat input penerimaan barang (*Stock IN* / Surat Jalan Supplier), wajib memilih **Supplier** dan **Sales Penanggung Jawab**.
  - Dilengkapi nomor referensi batch / faktur supplier (misal: `BATCH-202610-01` atau `SJ-ASTRA-8891`).
  - Sistem mencatat secara akurat sales mana yang membawa batch barang tersebut.

#### H. Faktur Pengembalian Barang Rusak ke Supplier (Retur Pembelian / Supplier Returns)
- **Pelacakan Barang Cacat per Batch & Sales**:
  - Jika teknisi menemukan barang rusak/cacat pabrik pada batch tertentu, bengkel dapat langsung mengidentifikasi sales mana yang bertanggung jawab untuk batch tersebut.
  - Terdapat tombol **1-Klik Retur** langsung dari riwayat batch barang masuk suku cadang (`/parts/{id}`).
- **Formulir & Nomor Faktur Retur Resmi**:
  - Format nomor faktur: `RET-YYYYMM-XXXXX` (misal: `RET-202610-00001`).
  - Menampung rincian cacat: *Pecah fisik*, *Karat/Aus*, *Cacat presisi pabrik*, *Segel rusak*, dsb.
  - Pilihan penyelesaian klaim: **Ganti Barang Baru (REPLACEMENT)** atau **Pengembalian Dana / Potong Tagihan (REFUND)**.
- **Otomasi Stok Gudang**:
  - Saat faktur retur dibuat: Stok barang rusak otomatis dipotong dari gudang (*OUT - Retur Barang Rusak*).
  - Saat klaim *REPLACEMENT* selesai: Stok barang pengganti otomatis dimasukkan kembali ke gudang (*IN - Penggantian Retur*).
  - Jika retur ditolak supplier: Stok dikembalikan ke gudang.
- **Komunikasi Sales WhatsApp & Cetak Dokumen Retur**:
  - Tombol **Kirim WA ke Sales**: Membuka WhatsApp langsung ke sales bersangkutan dengan pesan komplain formal yang otomatis terisi data nomor retur, nama part, jumlah, dan jenis cacat.
  - **Cetak Faktur Retur (A4)**: Halaman cetak resmi ber-Kop Surat dengan kolom verifikasi tanda tangan 3 pihak: Petugas Gudang Bengkel, Sales Supplier Pembawa Barang, dan Pimpinan/Owner Bengkel.

#### I. Kasir Pembayaran (POS) & Diskon Pembuatan Invoice
- **Kasir POS 1 Layar**: Input nominal pembayaran tunai (*Cash*), transfer bank, QRIS, atau kartu EDC.
- **Fitur Diskon Fleksibel**:
  - Diskon Persentase (%) atau Diskon Nominal Langsung (Rp) pada saat pembuatan faktur invoice kasir.
  - Perhitungan subtotal, diskon, dan grand total dilakukan secara otomatis dan transparan.
- **Faktur Cetak**: Halaman terisolasi (`resources/views/layouts/print.blade.php`) dengan styling `@media print` untuk mencetak invoice resmi A4 dan struk kasir.

#### J. Scanner Barcode Gudang & Verifikasi Pengeluaran Barang (`/scanner`)
- **Pusat Pemindaian Cerdas (Scanner Hub)**:
  - Mendukung kamera HP / Laptop / Tablet secara instan (HTML5 Video & `html5-qrcode`) dengan tombol nyalakan/matikan, ganti kamera belakang/depan, serta animasi garis laser.
  - Mendukung **Hardware Barcode Scanner Gun** (USB / Wireless Bluetooth) dengan *autofocus listener* dan deteksi instan saat tombol trigger scanner ditembakkan.
  - **Efek Suara Audio Terintegrasi (Web Audio API)**: Suara beep renyah (1200Hz) saat scan berhasil, dan nada alarm error buzzer (220Hz) saat terjadi kesalahan / salah barang.
- **Mode 1: Stock Opname (Audit Fisik Rak)**:
  - Scan barcode part &rarr; menampilkan data suku cadang, lokasi rak, dan stok sistem.
  - Input jumlah fisik aktual di rak (tombol pintas: *Sesuai Sistem*, *+1*, *+5*, *+10*, *Stok 0*).
  - Menghitung selisih real-time (*Surplus* / *Defisit*), menyimpan penyesuaian, dan mencatat `StockMovement` tipe `ADJUSTMENT`.
- **Mode 2: Penerimaan Barang Masuk (Stock IN via Scanner)**:
  - Scan barcode barang yang baru tiba dari supplier.
  - Input kuantitas masuk, update harga modal jika ada perubahan harga, pilih supplier & sales penanggung jawab dinamis, serta catat nomor surat jalan/batch.
- **Mode 3: Verifikasi Pengeluaran Barang Keluar (Work Order Dispatch Verification)**:
  - Pilih atau scan No. Work Order (SPK) teknisi (misal: `WO-2026-0001`).
  - Sistem memuat seluruh suku cadang yang disetujui untuk unit tersebut beserta lokasi raknya.
  - Petugas gudang memindai barcode fisik barang yang diambil dari rak:
    - **Barang Cocok**: Jumlah terverifikasi bertambah, baris part berkedip hijau, status berubah menjadi *Picked*.
    - **Barang Salah / Tidak Sesuai SPK**: Alarm suara error berbunyi nyaring, muncul modal peringatan merah: *"PERINGATAN SALAH BARANG! Barang ini bukan bagian dari Work Order ini! Jangan diserahkan ke teknisi!"*
  - **Progres Verifikasi**: Indikator persentase penyelesaian (0% &rarr; 100%).
  - **Cetak Bukti Pengeluaran Barang (Picking Slip / Surat Jalan Suku Cadang)**: Dokumen A4 resmi ber-Kop Surat dengan rincian suku cadang dan kolom tanda tangan Petugas Gudang (Menyerahkan) dan Teknisi (Menerima).
- **Mode 4: Pengeluaran Bebas (Stock OUT Langsung)**:
  - Scan barang &rarr; potong stok untuk keperluan internal bengkel, barang pecah/rusak di rak, atau sampel promosi.
- **Cetak Label Stiker Barcode Suku Cadang** (`/parts/{id}/barcode-print`):
  - Fitur cetak stiker label barcode ukuran standar rak/dus (70x40mm) berbasis SVG Code128 vector beresolusi tinggi.

#### K. Laporan Kinerja & Pengaturan Tema
- Rekapitulasi omset per rentang tanggal, produktivitas per teknisi, retur supplier, dan status pengerjaan.
- Tombol ekspor & cetak laporan.
- Konfigurasi identitas bengkel dan kustomisasi warna tema primer Bootstrap 5.

---

### 4. REST API UNTUK FLUTTER MOBILE (`/api/v1`)

Backend siap berkomunikasi dengan aplikasi mobile Flutter Android & iOS:

- `GET /api/v1/work-orders` - Mengambil daftar work order
- `GET /api/v1/work-orders/{id}` - Detail work order beserta inspeksi dan rincian item
- `POST /api/v1/work-orders/{id}/status` - Update status pengerjaan oleh teknisi mobile
- `GET /api/v1/parts` - Pencarian katalog suku cadang & ketersediaan stok
