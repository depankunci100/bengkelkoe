@extends('layouts.app')

@section('title', 'Scanner Barcode & Verifikasi Gudang')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 text-muted small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('parts.index') }}" class="text-decoration-none">Suku Cadang</a></li>
                <li class="breadcrumb-item active" aria-current="page">Scanner Barcode</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-upc-scan text-primary"></i> Scanner Gudang & Verifikasi Barang
        </h1>
        <p class="text-muted small mb-0">Scan barcode suku cadang via kamera HP/Laptop atau barcode scanner gun untuk Stock Opname & Verifikasi Pengeluaran.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('parts.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-box-seam me-1"></i> Data Sparepart
        </a>
        <a href="{{ route('work-orders.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-text me-1"></i> Daftar SPK
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="row g-4">
    <!-- MODE SELECTOR PILLS -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-2 p-md-3">
                <ul class="nav nav-pills nav-fill gap-2" id="scannerModeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $mode === 'opname' ? 'active' : '' }} py-2 py-md-3 d-flex align-items-center justify-content-center gap-2 fw-semibold" 
                                id="tab-opname" data-bs-toggle="pill" data-bs-target="#mode-opname" type="button" role="tab" onclick="switchScanMode('opname')">
                            <i class="bi bi-clipboard-check fs-5"></i>
                            <div>
                                <span class="d-block">Stock Opname</span>
                                <small class="text-muted fw-normal d-none d-md-inline">Audit Fisik & Penyesuaian</small>
                            </div>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $mode === 'stock_in' ? 'active' : '' }} py-2 py-md-3 d-flex align-items-center justify-content-center gap-2 fw-semibold" 
                                id="tab-stock-in" data-bs-toggle="pill" data-bs-target="#mode-stock-in" type="button" role="tab" onclick="switchScanMode('stock_in')">
                            <i class="bi bi-box-arrow-in-down fs-5"></i>
                            <div>
                                <span class="d-block">Barang Masuk (IN)</span>
                                <small class="text-muted fw-normal d-none d-md-inline">Penerimaan & Catat Batch Sales</small>
                            </div>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $mode === 'dispatch' ? 'active' : '' }} py-2 py-md-3 d-flex align-items-center justify-content-center gap-2 fw-semibold" 
                                id="tab-dispatch" data-bs-toggle="pill" data-bs-target="#mode-dispatch" type="button" role="tab" onclick="switchScanMode('dispatch')">
                            <i class="bi bi-shield-check fs-5"></i>
                            <div>
                                <span class="d-block">Verifikasi Pengeluaran SPK</span>
                                <small class="text-muted fw-normal d-none d-md-inline">Cek Fisik Part vs SPK Teknisi</small>
                            </div>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $mode === 'stock_out' ? 'active' : '' }} py-2 py-md-3 d-flex align-items-center justify-content-center gap-2 fw-semibold" 
                                id="tab-stock-out" data-bs-toggle="pill" data-bs-target="#mode-stock-out" type="button" role="tab" onclick="switchScanMode('stock_out')">
                            <i class="bi bi-box-arrow-up fs-5"></i>
                            <div>
                                <span class="d-block">Pengeluaran Bebas (OUT)</span>
                                <small class="text-muted fw-normal d-none d-md-inline">Internal / Rusak / Sampel</small>
                            </div>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- SCANNER CAMERA & INPUT CARD (LEFT) -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 sticky-top" style="top: 1rem; z-index: 10;">
            <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-camera-video-fill text-warning"></i> Viewfinder Scanner
                </span>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-light py-0 px-2" id="soundToggleBtn" onclick="toggleSound()" title="Aktif/Nonaktifkan Suara Beep">
                        <i class="bi bi-volume-up-fill" id="soundIcon"></i>
                    </button>
                    <span class="badge bg-success" id="cameraStatusBadge">Siap</span>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- CAMERA VIEWFINDER CONTAINER -->
                <div class="position-relative bg-black rounded overflow-hidden text-center mb-3" style="min-height: 240px; max-height: 320px;">
                    <div id="reader" style="width: 100%; height: 100%;"></div>

                    <!-- Placeholder / Start prompt if camera stopped -->
                    <div id="cameraPlaceholder" class="p-4 d-flex flex-column align-items-center justify-content-center h-100 position-absolute top-0 start-0 w-100 bg-dark text-light" style="z-index: 5;">
                        <i class="bi bi-upc-scan display-4 text-warning mb-2"></i>
                        <h6 class="fw-bold mb-1">Kamera Belum Aktif</h6>
                        <p class="small text-muted mb-3">Gunakan kamera HP/Laptop atau gunakan barcode scanner USB/Bluetooth di bawah.</p>
                        <button class="btn btn-primary btn-sm px-3" onclick="startScanner()">
                            <i class="bi bi-camera-fill me-1"></i> Nyalakan Kamera
                        </button>
                    </div>

                    <!-- Laser scanning animation line -->
                    <div id="scanLaserLine" class="position-absolute w-100 d-none" style="height: 2px; background: #22c55e; box-shadow: 0 0 10px #22c55e; top: 50%; z-index: 6; animation: scanAnim 2s infinite ease-in-out;"></div>
                </div>

                <!-- CAMERA CONTROLS -->
                <div class="d-flex gap-2 mb-3">
                    <button class="btn btn-sm btn-outline-primary flex-fill" id="startCamBtn" onclick="startScanner()">
                        <i class="bi bi-play-circle me-1"></i> Nyalakan Kamera
                    </button>
                    <button class="btn btn-sm btn-outline-secondary d-none" id="switchCamBtn" onclick="switchCamera()">
                        <i class="bi bi-arrow-repeat me-1"></i> Balik Kamera
                    </button>
                    <button class="btn btn-sm btn-outline-danger d-none" id="stopCamBtn" onclick="stopScanner()">
                        <i class="bi bi-stop-circle me-1"></i> Matikan Kamera
                    </button>
                </div>

                <!-- HARDWARE BARCODE SCANNER / MANUAL INPUT -->
                <div class="border rounded p-2 bg-light">
                    <label class="form-label small fw-bold text-dark mb-1 d-flex justify-content-between">
                        <span><i class="bi bi-keyboard me-1 text-primary"></i> Input Scanner Barcode Gun / Manual</span>
                        <span class="text-success small"><i class="bi bi-lightning-charge-fill"></i> Auto-Focus</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-upc"></i></span>
                        <input type="text" id="barcodeInput" class="form-control form-control-lg fw-bold text-primary font-monospace" 
                               placeholder="Scan barcode / ketik kode..." autocomplete="off" autofocus>
                        <button class="btn btn-primary" type="button" onclick="handleManualSearch()">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">
                        *Scanner gun USB/Bluetooth akan otomatis mengisi dan menekan Enter.
                    </small>
                </div>

                <!-- LAST SCANNED RESULT QUICK PILL -->
                <div id="lastScannedNotice" class="alert alert-secondary py-2 px-3 mt-3 mb-0 d-none small d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Terakhir discan:</span>
                        <strong id="lastScannedCode" class="ms-1 font-monospace">-</strong>
                    </div>
                    <span id="lastScannedTime" class="text-muted small">-</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN INTERACTIVE CONTENT AREA (RIGHT) -->
    <div class="col-lg-7">
        <div class="tab-content" id="scannerModeTabContent">
            
            <!-- ============================================================== -->
            <!-- TAB 1: STOCK OPNAME (PENYESUAIAN FISIK) -->
            <!-- ============================================================== -->
            <div class="tab-pane fade {{ $mode === 'opname' ? 'show active' : '' }}" id="mode-opname" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-clipboard-check text-primary"></i> Stock Opname / Audit Fisik
                            </h5>
                            <small class="text-muted">Bandingkan jumlah fisik di rak dengan stok di sistem secara cepat.</small>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Mode Opname</span>
                    </div>
                    <div class="card-body">
                        <!-- EMPTY STATE BEFORE SCAN -->
                        <div id="opnameEmptyState" class="text-center py-5">
                            <div class="p-3 bg-light rounded-circle d-inline-block text-muted mb-3">
                                <i class="bi bi-upc-scan display-4"></i>
                            </div>
                            <h6 class="fw-semibold text-secondary">Silakan Scan Barcode Suku Cadang</h6>
                            <p class="text-muted small mb-0">Arahkan barcode part di depan kamera atau gunakan scanner gun untuk memulai penghitungan stok.</p>
                        </div>

                        <!-- PART DETAIL & ADJUSTMENT FORM (VISIBLE AFTER SCAN) -->
                        <div id="opnameDetailArea" class="d-none">
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <span class="badge bg-secondary mb-1" id="opnamePartCategory">Kategori</span>
                                            <h5 class="fw-bold text-dark mb-1" id="opnamePartName">Nama Suku Cadang</h5>
                                            <div class="font-monospace text-muted small">
                                                Part No: <span id="opnamePartNumber" class="fw-bold text-dark">-</span> | 
                                                Barcode: <span id="opnameBarcode" class="text-primary">-</span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted small d-block">Lokasi Rak</span>
                                            <span class="badge bg-dark fs-6" id="opnameLocation">-</span>
                                        </div>
                                    </div>
                                    <hr class="my-2 text-muted">
                                    <div class="row g-2 text-center pt-1">
                                        <div class="col-4">
                                            <div class="bg-white p-2 rounded shadow-sm">
                                                <small class="text-muted d-block">Stok Sistem</small>
                                                <span class="h4 fw-bold text-primary mb-0" id="opnameCurrentStock">0</span>
                                                <small class="text-muted" id="opnameUnit">Pcs</small>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-white p-2 rounded shadow-sm">
                                                <small class="text-muted d-block">Harga Modal</small>
                                                <span class="fw-bold text-dark mb-0" id="opnameCostPrice">Rp 0</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="bg-white p-2 rounded shadow-sm">
                                                <small class="text-muted d-block">Harga Jual</small>
                                                <span class="fw-bold text-success mb-0" id="opnameSellingPrice">Rp 0</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form id="opnameForm" onsubmit="submitOpname(event)">
                                <input type="hidden" id="opnamePartId" name="part_id">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jumlah Fisik Aktual (Hasil Hitung Rak)</label>
                                    <div class="input-group input-group-lg">
                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustOpnameQty(-1)">-1</button>
                                        <input type="number" id="opnamePhysicalStock" name="physical_stock" 
                                               class="form-control text-center fw-bold fs-3 text-primary" 
                                               min="0" required oninput="calculateOpnameDiff()">
                                        <button class="btn btn-outline-secondary" type="button" onclick="adjustOpnameQty(1)">+1</button>
                                    </div>
                                    <!-- Quick Buttons -->
                                    <div class="d-flex gap-2 mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setOpnameSameAsSystem()">
                                            <i class="bi bi-check2"></i> Sesuai Sistem
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustOpnameQty(5)">+5</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustOpnameQty(10)">+10</button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="setOpnameZero()">Stok 0 (Habis)</button>
                                    </div>
                                </div>

                                <!-- Difference indicator -->
                                <div id="opnameDiffCard" class="alert alert-info py-2 px-3 mb-3 d-flex justify-content-between align-items-center">
                                    <span>Selisih dengan Sistem:</span>
                                    <strong id="opnameDiffValue" class="fs-6">0 Unit (Tepat)</strong>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Catatan Opname / Alasan Penyesuaian</label>
                                    <input type="text" id="opnameNotes" name="notes" class="form-control" placeholder="Contoh: Audit fisik rak bulanan / Barang rusak / Selisih hitung">
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg flex-fill shadow-sm" id="btnSubmitOpname">
                                        <i class="bi bi-save me-1"></i> Simpan Penyesuaian Stok
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetScanResult('opname')">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 2: BARANG MASUK (STOCK IN / PENERIMAAN SUPPLIER) -->
            <!-- ============================================================== -->
            <div class="tab-pane fade {{ $mode === 'stock_in' ? 'show active' : '' }}" id="mode-stock-in" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-in-down text-success"></i> Penerimaan Barang Masuk (Stock IN)
                            </h5>
                            <small class="text-muted">Scan barang datang, input batch surat jalan, dan catat sales penanggung jawab.</small>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Stock IN</span>
                    </div>
                    <div class="card-body">
                        <!-- EMPTY STATE BEFORE SCAN -->
                        <div id="stockInEmptyState" class="text-center py-5">
                            <div class="p-3 bg-light rounded-circle d-inline-block text-muted mb-3">
                                <i class="bi bi-box-arrow-in-down display-4 text-success"></i>
                            </div>
                            <h6 class="fw-semibold text-secondary">Silakan Scan Barcode Barang Masuk</h6>
                            <p class="text-muted small mb-0">Scan barcode suku cadang yang baru tiba dari supplier untuk mencatat stok masuk.</p>
                        </div>

                        <!-- PART DETAIL & STOCK IN FORM -->
                        <div id="stockInDetailArea" class="d-none">
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-success mb-1" id="stockInPartCategory">Kategori</span>
                                            <h5 class="fw-bold text-dark mb-1" id="stockInPartName">Nama Suku Cadang</h5>
                                            <div class="font-monospace text-muted small">
                                                Part No: <span id="stockInPartNumber" class="fw-bold text-dark">-</span> | 
                                                Stok Saat Ini: <strong id="stockInCurrentStock" class="text-primary">0</strong>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted small d-block">Lokasi Rak</span>
                                            <span class="badge bg-dark fs-6" id="stockInLocation">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form id="stockInForm" onsubmit="submitStockIn(event)">
                                <input type="hidden" id="stockInPartId" name="part_id">

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Jumlah Barang Masuk <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" id="stockInQuantity" name="quantity" class="form-control form-control-lg fw-bold text-success" value="1" min="1" required>
                                            <span class="input-group-text" id="stockInUnit">Pcs</span>
                                        </div>
                                        <div class="d-flex gap-1 mt-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="adjustStockInQty(1)">+1</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="adjustStockInQty(5)">+5</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="adjustStockInQty(10)">+10</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="adjustStockInQty(20)">+20</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Harga Beli Modal (Rp)</label>
                                        <input type="number" id="stockInCostPrice" name="cost_price" class="form-control form-control-lg" placeholder="0" min="0">
                                        <small class="text-muted">Biarkan jika harga modal tidak berubah.</small>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Supplier Pembawa Barang</label>
                                        <select id="stockInSupplierId" name="supplier_id" class="form-select" onchange="onSupplierChangeInStockIn()">
                                            <option value="">-- Pilih Supplier --</option>
                                            @foreach($suppliers as $sup)
                                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Sales Penanggung Jawab (PIC)</label>
                                        <select id="stockInSupplierSalesId" name="supplier_sales_id" class="form-select">
                                            <option value="">-- Pilih Sales --</option>
                                        </select>
                                        <small class="text-muted">Penting untuk pelacakan retur jika ada cacat barang.</small>
                                    </div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">No. Batch / No. Surat Jalan</label>
                                        <input type="text" id="stockInBatchReference" name="batch_reference" class="form-control font-monospace" placeholder="Contoh: SJ-202610-098">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Catatan Penerimaan</label>
                                        <input type="text" id="stockInNotes" name="notes" class="form-control" placeholder="Keterangan tambahan penerimaan">
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success btn-lg flex-fill shadow-sm" id="btnSubmitStockIn">
                                        <i class="bi bi-box-arrow-in-down me-1"></i> Simpan Stok Masuk
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetScanResult('stock_in')">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 3: VERIFIKASI PENGELUARAN BARANG WORK ORDER (SPK) -->
            <!-- ============================================================== -->
            <div class="tab-pane fade {{ $mode === 'dispatch' ? 'show active' : '' }}" id="mode-dispatch" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-shield-check text-info"></i> Verifikasi Pengeluaran Suku Cadang (SPK)
                            </h5>
                            <small class="text-muted">Pastikan barang yang diambil dari rak sesuai dengan Work Order sebelum diserahkan ke teknisi.</small>
                        </div>
                        <span class="badge bg-info-subtle text-info border border-info-subtle">Dispatch Verification</span>
                    </div>
                    <div class="card-body">
                        <!-- SELECT WORK ORDER -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih / Scan Nomor Work Order (SPK)</label>
                            <div class="input-group">
                                <select id="dispatchWoSelect" class="form-select form-select-lg" onchange="onWorkOrderSelected()">
                                    <option value="">-- Pilih Work Order yang Sedang Dikerjakan --</option>
                                    @foreach($activeWorkOrders as $wo)
                                        <option value="{{ $wo->id }}" {{ $selectedWorkOrder && $selectedWorkOrder->id == $wo->id ? 'selected' : '' }}>
                                            {{ $wo->wo_number }} - {{ $wo->customer->name }} ({{ $wo->vehicle->license_plate }} - {{ $wo->vehicle->brand }} {{ $wo->vehicle->model }})
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-primary" type="button" onclick="loadWorkOrderDetails()">
                                    <i class="bi bi-arrow-clockwise"></i> Muat
                                </button>
                            </div>
                        </div>

                        <!-- WORK ORDER DETAILS & ITEM VERIFICATION LIST -->
                        <div id="dispatchWoContent" class="{{ $selectedWorkOrder ? '' : 'd-none' }}">
                            <!-- WO HEADER SUMMARY -->
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                        <div>
                                            <span class="badge bg-dark fs-6" id="dispatchWoNumber">{{ $selectedWorkOrder ? $selectedWorkOrder->wo_number : '-' }}</span>
                                            <span class="badge bg-primary ms-1" id="dispatchWoStatus">{{ $selectedWorkOrder ? $selectedWorkOrder->status : '-' }}</span>
                                        </div>
                                        <div id="dispatchCompleteBadge" class="{{ $selectedWorkOrder && $selectedWorkOrder->allApprovedPartsVerified() ? '' : 'd-none' }}">
                                            <span class="badge bg-success py-2 px-3"><i class="bi bi-check-all me-1"></i>SEMUA ITEM TERVERIFIKASI</span>
                                        </div>
                                    </div>
                                    <div class="row g-2 small text-muted">
                                        <div class="col-md-4">
                                            <strong>Pelanggan:</strong> <span id="dispatchCustomerName">{{ $selectedWorkOrder ? $selectedWorkOrder->customer->name : '-' }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Kendaraan:</strong> <span id="dispatchVehicleInfo">{{ $selectedWorkOrder ? ($selectedWorkOrder->vehicle->license_plate . ' - ' . $selectedWorkOrder->vehicle->model) : '-' }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Teknisi:</strong> <span id="dispatchTechnicianName">{{ $selectedWorkOrder && $selectedWorkOrder->technician ? $selectedWorkOrder->technician->name : 'Belum Ditugaskan' }}</span>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between small fw-bold mb-1">
                                            <span>Progres Verifikasi Suku Cadang:</span>
                                            <span id="dispatchProgressText">0 / 0 Terverifikasi (0%)</span>
                                        </div>
                                        <div class="progress" style="height: 10px;">
                                            <div id="dispatchProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- INSTRUCTION ALERT -->
                            <div class="alert alert-primary py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                                <div>
                                    <strong>Instruksi Petugas Gudang:</strong> Arahkan kamera atau scan barcode barang fisik yang diambil dari rak. Sistem akan memverifikasi kecocokan barang secara otomatis dengan alarm audio.
                                </div>
                            </div>

                            <!-- ITEMS TABLE -->
                            <div class="table-responsive border rounded mb-3">
                                <table class="table table-hover align-middle mb-0" id="dispatchItemsTable">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Part Number & Barcode</th>
                                            <th>Nama Suku Cadang</th>
                                            <th class="text-center">Rak</th>
                                            <th class="text-center">Kebutuhan SPK</th>
                                            <th class="text-center">Terverifikasi</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dispatchItemsTbody">
                                        @if($selectedWorkOrder)
                                            @foreach($selectedWorkOrder->items->where('type', 'PART') as $item)
                                                <tr id="item-row-{{ $item->id }}" class="{{ $item->isFullyVerified() ? 'table-success bg-opacity-25' : '' }}">
                                                    <td>
                                                        <div class="fw-bold font-monospace">{{ $item->part ? $item->part->part_number : '-' }}</div>
                                                        <small class="text-muted font-monospace">{{ $item->part ? $item->part->effective_barcode : '-' }}</small>
                                                    </td>
                                                    <td>
                                                        <div class="fw-semibold">{{ $item->item_name }}</div>
                                                        <small class="text-muted">{{ $item->part ? $item->part->brand : '' }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-secondary">{{ $item->part->location ?? '-' }}</span>
                                                    </td>
                                                    <td class="text-center fw-bold">{{ (float)$item->quantity }}</td>
                                                    <td class="text-center">
                                                        <span class="badge {{ $item->isFullyVerified() ? 'bg-success' : 'bg-warning text-dark' }} fs-6" id="item-verified-{{ $item->id }}">
                                                            {{ (float)$item->verified_quantity }} / {{ (float)$item->quantity }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center" id="item-status-{{ $item->id }}">
                                                        @if($item->isFullyVerified())
                                                            <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Picked</span>
                                                        @else
                                                            <span class="badge bg-outline-secondary border text-muted">Belum Diambil</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <!-- ACTION BUTTONS -->
                            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                <div>
                                    <a href="#" id="dispatchPrintSlipBtn" class="btn btn-outline-secondary" target="_blank">
                                        <i class="bi bi-printer me-1"></i> Cetak Picking Slip (Bukti Pengeluaran)
                                    </a>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-success" onclick="completeAllVerification()">
                                        <i class="bi bi-check2-all me-1"></i> Verifikasi Semua Sekaligus
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- EMPTY STATE IF NO WO SELECTED -->
                        <div id="dispatchNoWoState" class="text-center py-5 {{ $selectedWorkOrder ? 'd-none' : '' }}">
                            <i class="bi bi-file-earmark-check display-4 text-muted mb-2"></i>
                            <h6 class="text-secondary fw-semibold">Belum Ada Work Order yang Dipilih</h6>
                            <p class="text-muted small">Pilih Work Order dari dropdown di atas untuk memulai verifikasi barang keluar ke teknisi.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- TAB 4: PENGELUARAN BEBAS (STOCK OUT) -->
            <!-- ============================================================== -->
            <div class="tab-pane fade {{ $mode === 'stock_out' ? 'show active' : '' }}" id="mode-stock-out" role="tabpanel">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-box-arrow-up text-danger"></i> Pengeluaran Bebas (Direct Stock OUT)
                            </h5>
                            <small class="text-muted">Potong stok langsung untuk keperluan internal bengkel, barang rusak di rak, atau sampel.</small>
                        </div>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Stock OUT</span>
                    </div>
                    <div class="card-body">
                        <!-- EMPTY STATE BEFORE SCAN -->
                        <div id="stockOutEmptyState" class="text-center py-5">
                            <div class="p-3 bg-light rounded-circle d-inline-block text-muted mb-3">
                                <i class="bi bi-box-arrow-up display-4 text-danger"></i>
                            </div>
                            <h6 class="fw-semibold text-secondary">Silakan Scan Barcode Barang yang Ingin Dikeluarkan</h6>
                            <p class="text-muted small mb-0">Arahkan barcode suku cadang ke kamera atau scanner gun.</p>
                        </div>

                        <!-- DETAIL AREA -->
                        <div id="stockOutDetailArea" class="d-none">
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-danger mb-1" id="stockOutPartCategory">Kategori</span>
                                            <h5 class="fw-bold text-dark mb-1" id="stockOutPartName">Nama Suku Cadang</h5>
                                            <div class="font-monospace text-muted small">
                                                Part No: <span id="stockOutPartNumber" class="fw-bold text-dark">-</span> | 
                                                Stok Saat Ini: <strong id="stockOutCurrentStock" class="text-primary">0</strong>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted small d-block">Lokasi Rak</span>
                                            <span class="badge bg-dark fs-6" id="stockOutLocation">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form id="stockOutForm" onsubmit="submitStockOut(event)">
                                <input type="hidden" id="stockOutPartId" name="part_id">

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jumlah Barang Dikeluarkan <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" id="stockOutQuantity" name="quantity" class="form-control text-center fw-bold fs-3 text-danger" value="1" min="1" required>
                                        <span class="input-group-text" id="stockOutUnit">Pcs</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Alasan Pengeluaran <span class="text-danger">*</span></label>
                                    <select id="stockOutReasonPreset" class="form-select mb-2" onchange="onStockOutReasonPresetChange()">
                                        <option value="">-- Pilih Alasan Umum --</option>
                                        <option value="Pemakaian operasional / bengkel internal">Pemakaian operasional / bengkel internal</option>
                                        <option value="Barang rusak / pecah di rak gudang">Barang rusak / pecah di rak gudang</option>
                                        <option value="Sampel display / promosi">Sampel display / promosi</option>
                                        <option value="Pembersihan stok kadaluarsa / aus">Pembersihan stok kadaluarsa / aus</option>
                                        <option value="custom">Alasan Lainnya (Ketik Manual)</option>
                                    </select>
                                    <input type="text" id="stockOutNotes" name="notes" class="form-control" placeholder="Ketik alasan pengeluaran barang..." required>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-danger btn-lg flex-fill shadow-sm" id="btnSubmitStockOut">
                                        <i class="bi bi-box-arrow-up me-1"></i> Catat Pengeluaran Barang
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetScanResult('stock_out')">
                                        <i class="bi bi-x-circle"></i> Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RECENT ACTIVITY LOG OF THIS SCANNER SESSION -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-secondary"></i> Riwayat Aktivitas Sesi Scanner
                </span>
                <span class="badge bg-light text-muted border" id="sessionCountBadge">0 Aktivitas</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th style="width: 80px;">Waktu</th>
                                <th>Aktivitas & Part</th>
                                <th class="text-center">Perubahan</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="sessionActivityTbody">
                            @forelse($recentMovements as $mv)
                                <tr class="small">
                                    <td class="text-muted font-monospace">{{ $mv->created_at->format('H:i:s') }}</td>
                                    <td>
                                        <span class="fw-semibold">{{ $mv->part->name ?? '-' }}</span>
                                        <small class="text-muted d-block font-monospace">{{ $mv->part->part_number ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($mv->type === 'IN')
                                            <span class="badge bg-success-subtle text-success">+{{ $mv->quantity }}</span>
                                        @elseif($mv->type === 'OUT')
                                            <span class="badge bg-danger-subtle text-danger">-{{ $mv->quantity }}</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary">{{ $mv->after_stock }} (Audit)</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ Str::limit($mv->notes, 35) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">Belum ada aktivitas mutasi yang tercatat hari ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WARNING MODAL FOR WRONG PART SCANNED IN WORK ORDER -->
<div class="modal fade" id="wrongPartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i> BARANG TIDAK SESUAI SPK!
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="p-3 bg-danger-subtle rounded-circle d-inline-block text-danger mb-3">
                    <i class="bi bi-x-octagon display-3"></i>
                </div>
                <h5 class="fw-bold text-danger mb-2" id="wrongPartTitle">Peringatan Pengeluaran Barang</h5>
                <p class="text-dark mb-3" id="wrongPartMessage">Barang ini bukan merupakan bagian dari Work Order yang dipilih. Jangan serahkan ke teknisi!</p>
                <div class="card bg-light border-0 p-3 text-start small">
                    <div><strong>Part Number:</strong> <span id="wrongPartNumber" class="font-monospace">-</span></div>
                    <div><strong>Nama Barang:</strong> <span id="wrongPartName">-</span></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">
                    Mengerti & Kembalikan ke Rak
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Load html5-qrcode library for real-time camera scanning -->
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    // State management
    let activeMode = '{{ $mode }}';
    let currentPartData = null;
    let html5QrCode = null;
    let isCameraRunning = false;
    let currentCameraId = null;
    let availableCameras = [];
    let currentCameraIndex = 0;
    let soundEnabled = true;
    let scanCooldown = false;
    let lastScannedText = '';

    // Audio Context Synthesizer for instant beeps
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    function playSuccessBeep() {
        if (!soundEnabled) return;
        try {
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(1200, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.12);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.12);
        } catch (e) {
            console.error("Audio error", e);
        }
    }

    function playErrorBuzzer() {
        if (!soundEnabled) return;
        try {
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(220, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.35);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.35);
        } catch (e) {
            console.error("Audio error", e);
        }
    }

    function toggleSound() {
        soundEnabled = !soundEnabled;
        const icon = document.getElementById('soundIcon');
        const btn = document.getElementById('soundToggleBtn');
        if (soundEnabled) {
            icon.className = 'bi bi-volume-up-fill';
            btn.classList.replace('btn-outline-danger', 'btn-outline-light');
            playSuccessBeep();
        } else {
            icon.className = 'bi bi-volume-mute-fill';
            btn.classList.replace('btn-outline-light', 'btn-outline-danger');
        }
    }

    // Switch between scanning modes
    function switchScanMode(mode) {
        activeMode = mode;
        const url = new URL(window.location.href);
        url.searchParams.set('mode', mode);
        window.history.replaceState({}, '', url);

        // Autofocus barcode input
        const barcodeInput = document.getElementById('barcodeInput');
        if (barcodeInput) {
            barcodeInput.focus();
        }

        if (mode === 'dispatch') {
            const woSelect = document.getElementById('dispatchWoSelect');
            if (woSelect && woSelect.value) {
                updatePrintSlipLink(woSelect.value);
            }
        }
    }

    // Camera scanner controls via Html5Qrcode
    async function startScanner() {
        try {
            const placeholder = document.getElementById('cameraPlaceholder');
            const laser = document.getElementById('scanLaserLine');
            const startBtn = document.getElementById('startCamBtn');
            const stopBtn = document.getElementById('stopCamBtn');
            const switchBtn = document.getElementById('switchCamBtn');
            const statusBadge = document.getElementById('cameraStatusBadge');

            if (!html5QrCode) {
                html5QrCode = new Html5Qrcode("reader");
            }

            availableCameras = await Html5Qrcode.getCameras();
            if (!availableCameras || availableCameras.length === 0) {
                alert("Tidak ada perangkat kamera yang terdeteksi.");
                return;
            }

            // Prefer back camera (environment) if available
            let cameraToUse = availableCameras[0].id;
            for (let i = 0; i < availableCameras.length; i++) {
                const label = availableCameras[i].label.toLowerCase();
                if (label.includes('back') || label.includes('rear') || label.includes('environment')) {
                    cameraToUse = availableCameras[i].id;
                    currentCameraIndex = i;
                    break;
                }
            }

            currentCameraId = cameraToUse;

            const config = {
                fps: 15,
                qrbox: { width: 250, height: 180 },
                aspectRatio: 1.333334
            };

            await html5QrCode.start(
                currentCameraId,
                config,
                onBarcodeScanned,
                (errorMessage) => {
                    // Ignore transient frame errors
                }
            );

            isCameraRunning = true;
            placeholder.classList.add('d-none');
            laser.classList.remove('d-none');
            startBtn.classList.add('d-none');
            stopBtn.classList.remove('d-none');
            if (availableCameras.length > 1) {
                switchBtn.classList.remove('d-none');
            }
            statusBadge.className = 'badge bg-success';
            statusBadge.innerText = 'Kamera Aktif';

        } catch (err) {
            console.error("Camera start failed", err);
            alert("Gagal mengakses kamera: " + (err.message || err));
        }
    }

    async function stopScanner() {
        if (html5QrCode && isCameraRunning) {
            try {
                await html5QrCode.stop();
                isCameraRunning = false;
                document.getElementById('cameraPlaceholder').classList.remove('d-none');
                document.getElementById('scanLaserLine').classList.add('d-none');
                document.getElementById('startCamBtn').classList.remove('d-none');
                document.getElementById('stopCamBtn').classList.add('d-none');
                document.getElementById('switchCamBtn').classList.add('d-none');
                document.getElementById('cameraStatusBadge').className = 'badge bg-secondary';
                document.getElementById('cameraStatusBadge').innerText = 'Standby';
            } catch (err) {
                console.error("Stop camera error", err);
            }
        }
    }

    async function switchCamera() {
        if (!isCameraRunning || availableCameras.length <= 1) return;
        await stopScanner();
        currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
        currentCameraId = availableCameras[currentCameraIndex].id;
        await startScanner();
    }

    // Barcode scanned callback
    function onBarcodeScanned(decodedText, decodedResult) {
        if (scanCooldown && lastScannedText === decodedText) {
            return;
        }

        scanCooldown = true;
        lastScannedText = decodedText;
        setTimeout(() => { scanCooldown = false; }, 1600);

        processBarcodeCode(decodedText);
    }

    // Process scanned code
    function processBarcodeCode(code) {
        code = code.trim();
        if (!code) return;

        // Show last scanned pill
        const pill = document.getElementById('lastScannedNotice');
        const codeSpan = document.getElementById('lastScannedCode');
        const timeSpan = document.getElementById('lastScannedTime');
        pill.classList.remove('d-none');
        codeSpan.innerText = code;
        timeSpan.innerText = new Date().toLocaleTimeString();

        // Mode 3: Dispatch verification for Work Order
        if (activeMode === 'dispatch') {
            const woId = document.getElementById('dispatchWoSelect').value;
            if (!woId) {
                playErrorBuzzer();
                alert("Pilih Work Order terlebih dahulu sebelum scan barang keluar!");
                return;
            }
            verifyDispatchBarcode(woId, code);
            return;
        }

        // Mode 1, 2, 4: Lookup Part
        lookupPart(code);
    }

    // Lookup part details from API
    async function lookupPart(code) {
        try {
            const response = await fetch(`{{ route('scanner.lookup') }}?code=${encodeURIComponent(code)}`);
            const data = await response.json();

            if (!data.success) {
                playErrorBuzzer();
                alert(data.message || "Barang tidak ditemukan di sistem bengkel.");
                return;
            }

            playSuccessBeep();
            currentPartData = data.part;

            if (activeMode === 'opname') {
                populateOpnameView(data.part);
            } else if (activeMode === 'stock_in') {
                populateStockInView(data.part);
            } else if (activeMode === 'stock_out') {
                populateStockOutView(data.part);
            }

        } catch (err) {
            console.error("Lookup error", err);
            playErrorBuzzer();
            alert("Terjadi kesalahan saat mencari suku cadang.");
        }
    }

    // Populate Opname View
    function populateOpnameView(part) {
        document.getElementById('opnameEmptyState').classList.add('d-none');
        document.getElementById('opnameDetailArea').classList.remove('d-none');

        document.getElementById('opnamePartId').value = part.id;
        document.getElementById('opnamePartName').innerText = `${part.brand} - ${part.name}`;
        document.getElementById('opnamePartCategory').innerText = part.category;
        document.getElementById('opnamePartNumber').innerText = part.part_number;
        document.getElementById('opnameBarcode').innerText = part.barcode;
        document.getElementById('opnameLocation').innerText = part.location;
        document.getElementById('opnameCurrentStock').innerText = part.stock;
        document.getElementById('opnameUnit').innerText = part.unit;
        document.getElementById('opnameCostPrice').innerText = 'Rp ' + Number(part.cost_price).toLocaleString('id-ID');
        document.getElementById('opnameSellingPrice').innerText = 'Rp ' + Number(part.selling_price).toLocaleString('id-ID');

        // Set default physical stock equal to current stock
        document.getElementById('opnamePhysicalStock').value = part.stock;
        document.getElementById('opnameNotes').value = '';
        calculateOpnameDiff();
    }

    function calculateOpnameDiff() {
        if (!currentPartData) return;
        const current = parseInt(currentPartData.stock) || 0;
        const physical = parseInt(document.getElementById('opnamePhysicalStock').value) || 0;
        const diff = physical - current;

        const card = document.getElementById('opnameDiffCard');
        const val = document.getElementById('opnameDiffValue');

        if (diff === 0) {
            card.className = 'alert alert-success py-2 px-3 mb-3 d-flex justify-content-between align-items-center';
            val.innerText = '0 Unit (Tepat Sesuai Sistem)';
        } else if (diff > 0) {
            card.className = 'alert alert-info py-2 px-3 mb-3 d-flex justify-content-between align-items-center';
            val.innerText = `+${diff} Unit (Surplus / Lebih Fisik)`;
        } else {
            card.className = 'alert alert-warning py-2 px-3 mb-3 d-flex justify-content-between align-items-center';
            val.innerText = `${diff} Unit (Defisit / Kurang Fisik)`;
        }
    }

    function adjustOpnameQty(delta) {
        const input = document.getElementById('opnamePhysicalStock');
        let val = (parseInt(input.value) || 0) + delta;
        input.value = Math.max(0, val);
        calculateOpnameDiff();
    }

    function setOpnameSameAsSystem() {
        if (currentPartData) {
            document.getElementById('opnamePhysicalStock').value = currentPartData.stock;
            calculateOpnameDiff();
        }
    }

    function setOpnameZero() {
        document.getElementById('opnamePhysicalStock').value = 0;
        calculateOpnameDiff();
    }

    async function submitOpname(e) {
        e.preventDefault();
        const partId = document.getElementById('opnamePartId').value;
        const physicalStock = document.getElementById('opnamePhysicalStock').value;
        const notes = document.getElementById('opnameNotes').value;
        const btn = document.getElementById('btnSubmitOpname');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        try {
            const response = await fetch(`{{ route('scanner.opname') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    part_id: partId,
                    physical_stock: physicalStock,
                    notes: notes
                })
            });

            const res = await response.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Penyesuaian Stok';

            if (res.success) {
                playSuccessBeep();
                appendSessionActivity(res.movement.part_name, res.movement.part_number, res.movement.diff_text, 'OPNAME', res.movement.after_stock, notes);
                alert(res.message);
                resetScanResult('opname');
            } else {
                playErrorBuzzer();
                alert(res.message || "Gagal menyimpan opname.");
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i> Simpan Penyesuaian Stok';
            playErrorBuzzer();
            alert("Kesalahan jaringan saat menyimpan opname.");
        }
    }

    // Populate Stock IN View
    function populateStockInView(part) {
        document.getElementById('stockInEmptyState').classList.add('d-none');
        document.getElementById('stockInDetailArea').classList.remove('d-none');

        document.getElementById('stockInPartId').value = part.id;
        document.getElementById('stockInPartName').innerText = `${part.brand} - ${part.name}`;
        document.getElementById('stockInPartCategory').innerText = part.category;
        document.getElementById('stockInPartNumber').innerText = part.part_number;
        document.getElementById('stockInLocation').innerText = part.location;
        document.getElementById('stockInCurrentStock').innerText = `${part.stock} ${part.unit}`;
        document.getElementById('stockInUnit').innerText = part.unit;
        document.getElementById('stockInCostPrice').value = part.cost_price;
        document.getElementById('stockInQuantity').value = 1;

        if (part.supplier_id) {
            document.getElementById('stockInSupplierId').value = part.supplier_id;
            onSupplierChangeInStockIn(part.sales_list);
        } else {
            document.getElementById('stockInSupplierId').value = '';
            document.getElementById('stockInSupplierSalesId').innerHTML = '<option value="">-- Pilih Sales --</option>';
        }
    }

    function adjustStockInQty(delta) {
        const input = document.getElementById('stockInQuantity');
        let val = (parseInt(input.value) || 0) + delta;
        input.value = Math.max(1, val);
    }

    async function onSupplierChangeInStockIn(prefilledSales = null) {
        const supId = document.getElementById('stockInSupplierId').value;
        const salesSelect = document.getElementById('stockInSupplierSalesId');
        salesSelect.innerHTML = '<option value="">-- Memuat Sales... --</option>';

        if (!supId) {
            salesSelect.innerHTML = '<option value="">-- Pilih Sales --</option>';
            return;
        }

        if (prefilledSales && prefilledSales.length > 0) {
            populateSalesDropdown(prefilledSales);
            return;
        }

        try {
            const res = await fetch(`/api/suppliers/${supId}/sales`);
            const sales = await res.json();
            populateSalesDropdown(sales);
        } catch (e) {
            salesSelect.innerHTML = '<option value="">-- Gagal memuat sales --</option>';
        }
    }

    function populateSalesDropdown(sales) {
        const salesSelect = document.getElementById('stockInSupplierSalesId');
        salesSelect.innerHTML = '<option value="">-- Pilih Sales Penanggung Jawab --</option>';
        sales.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.innerText = `${s.name} ${s.phone ? '(' + s.phone + ')' : ''}`;
            salesSelect.appendChild(opt);
        });
    }

    async function submitStockIn(e) {
        e.preventDefault();
        const partId = document.getElementById('stockInPartId').value;
        const quantity = document.getElementById('stockInQuantity').value;
        const costPrice = document.getElementById('stockInCostPrice').value;
        const supplierId = document.getElementById('stockInSupplierId').value;
        const supplierSalesId = document.getElementById('stockInSupplierSalesId').value;
        const batchReference = document.getElementById('stockInBatchReference').value;
        const notes = document.getElementById('stockInNotes').value;
        const btn = document.getElementById('btnSubmitStockIn');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        try {
            const response = await fetch(`{{ route('scanner.stock-in') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    part_id: partId,
                    quantity: quantity,
                    cost_price: costPrice,
                    supplier_id: supplierId,
                    supplier_sales_id: supplierSalesId,
                    batch_reference: batchReference,
                    notes: notes
                })
            });

            const res = await response.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-in-down me-1"></i> Simpan Stok Masuk';

            if (res.success) {
                playSuccessBeep();
                appendSessionActivity(res.movement.part_name, res.movement.part_number, res.movement.qty, 'IN', res.movement.after_stock, `Batch: ${res.movement.batch} | Sales: ${res.movement.sales_name}`);
                alert(res.message);
                resetScanResult('stock_in');
            } else {
                playErrorBuzzer();
                alert(res.message || "Gagal mencatat barang masuk.");
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-in-down me-1"></i> Simpan Stok Masuk';
            playErrorBuzzer();
            alert("Kesalahan jaringan saat menyimpan barang masuk.");
        }
    }

    // Populate Stock OUT View
    function populateStockOutView(part) {
        document.getElementById('stockOutEmptyState').classList.add('d-none');
        document.getElementById('stockOutDetailArea').classList.remove('d-none');

        document.getElementById('stockOutPartId').value = part.id;
        document.getElementById('stockOutPartName').innerText = `${part.brand} - ${part.name}`;
        document.getElementById('stockOutPartCategory').innerText = part.category;
        document.getElementById('stockOutPartNumber').innerText = part.part_number;
        document.getElementById('stockOutLocation').innerText = part.location;
        document.getElementById('stockOutCurrentStock').innerText = `${part.stock} ${part.unit}`;
        document.getElementById('stockOutUnit').innerText = part.unit;
        document.getElementById('stockOutQuantity').value = 1;
        document.getElementById('stockOutReasonPreset').value = '';
        document.getElementById('stockOutNotes').value = '';
    }

    function onStockOutReasonPresetChange() {
        const preset = document.getElementById('stockOutReasonPreset').value;
        const notesInput = document.getElementById('stockOutNotes');
        if (preset && preset !== 'custom') {
            notesInput.value = preset;
        } else if (preset === 'custom') {
            notesInput.value = '';
            notesInput.focus();
        }
    }

    async function submitStockOut(e) {
        e.preventDefault();
        const partId = document.getElementById('stockOutPartId').value;
        const quantity = document.getElementById('stockOutQuantity').value;
        const notes = document.getElementById('stockOutNotes').value;
        const btn = document.getElementById('btnSubmitStockOut');

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        try {
            const response = await fetch(`{{ route('scanner.stock-out') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    part_id: partId,
                    quantity: quantity,
                    notes: notes
                })
            });

            const res = await response.json();
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-up me-1"></i> Catat Pengeluaran Barang';

            if (res.success) {
                playSuccessBeep();
                appendSessionActivity(res.movement.part_name, res.movement.part_number, res.movement.qty, 'OUT', res.movement.after_stock, notes);
                alert(res.message);
                resetScanResult('stock_out');
            } else {
                playErrorBuzzer();
                alert(res.message || "Gagal mencatat pengeluaran barang.");
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-box-arrow-up me-1"></i> Catat Pengeluaran Barang';
            playErrorBuzzer();
            alert("Kesalahan jaringan saat menyimpan pengeluaran barang.");
        }
    }

    // Dispatch Verification (Work Order)
    function onWorkOrderSelected() {
        const woId = document.getElementById('dispatchWoSelect').value;
        if (!woId) {
            document.getElementById('dispatchWoContent').classList.add('d-none');
            document.getElementById('dispatchNoWoState').classList.remove('d-none');
            return;
        }
        updatePrintSlipLink(woId);
        loadWorkOrderDetails();
    }

    function updatePrintSlipLink(woId) {
        const btn = document.getElementById('dispatchPrintSlipBtn');
        if (btn) {
            btn.href = `/work-orders/${woId}/picking-slip`;
        }
    }

    async function loadWorkOrderDetails() {
        const woId = document.getElementById('dispatchWoSelect').value;
        if (!woId) return;

        try {
            const response = await fetch(`{{ route('scanner.work-order') }}?wo=${woId}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || "Work Order tidak ditemukan.");
                return;
            }

            renderWorkOrderDispatch(data.work_order);

        } catch (err) {
            console.error("Load WO error", err);
            alert("Gagal memuat data Work Order.");
        }
    }

    function renderWorkOrderDispatch(wo) {
        document.getElementById('dispatchNoWoState').classList.add('d-none');
        document.getElementById('dispatchWoContent').classList.remove('d-none');

        document.getElementById('dispatchWoNumber').innerText = wo.wo_number;
        document.getElementById('dispatchWoStatus').innerText = wo.status;
        document.getElementById('dispatchCustomerName').innerText = wo.customer_name;
        document.getElementById('dispatchVehicleInfo').innerText = `${wo.vehicle_plate} - ${wo.vehicle_model}`;
        document.getElementById('dispatchTechnicianName').innerText = wo.technician_name;

        // Progress
        updateDispatchProgressBar(wo.progress);

        // Render Items Table
        const tbody = document.getElementById('dispatchItemsTbody');
        tbody.innerHTML = '';

        if (!wo.items || wo.items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada suku cadang pada Work Order ini.</td></tr>';
            return;
        }

        wo.items.forEach(item => {
            const isFull = item.is_verified;
            const tr = document.createElement('tr');
            tr.id = `item-row-${item.id}`;
            if (isFull) {
                tr.className = 'table-success bg-opacity-25';
            }

            tr.innerHTML = `
                <td>
                    <div class="fw-bold font-monospace">${item.part_number}</div>
                    <small class="text-muted font-monospace">${item.barcode}</small>
                </td>
                <td>
                    <div class="fw-semibold">${item.name}</div>
                </td>
                <td class="text-center">
                    <span class="badge bg-secondary">${item.location}</span>
                </td>
                <td class="text-center fw-bold">${item.quantity}</td>
                <td class="text-center">
                    <span class="badge ${isFull ? 'bg-success' : 'bg-warning text-dark'} fs-6" id="item-verified-${item.id}">
                        ${item.verified_quantity} / ${item.quantity}
                    </span>
                </td>
                <td class="text-center" id="item-status-${item.id}">
                    ${isFull ? '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Picked</span>' : '<span class="badge border text-muted">Belum Diambil</span>'}
                </td>
            `;
            tbody.appendChild(tr);
        });

        updatePrintSlipLink(wo.id);
    }

    function updateDispatchProgressBar(progress) {
        const text = document.getElementById('dispatchProgressText');
        const bar = document.getElementById('dispatchProgressBar');
        const completeBadge = document.getElementById('dispatchCompleteBadge');

        text.innerText = `${progress.verified} / ${progress.total} Terverifikasi (${progress.percent}%)`;
        bar.style.width = `${progress.percent}%`;

        if (progress.is_complete) {
            bar.classList.remove('progress-bar-animated');
            completeBadge.classList.remove('d-none');
        } else {
            bar.classList.add('progress-bar-animated');
            completeBadge.classList.add('d-none');
        }
    }

    // Verify item scanned against Work Order
    async function verifyDispatchBarcode(woId, code) {
        try {
            const response = await fetch(`{{ route('scanner.verify-item') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    work_order_id: woId,
                    barcode_or_code: code,
                    quantity: 1
                })
            });

            const res = await response.json();

            if (!res.success) {
                playErrorBuzzer();
                if (res.error_type === 'NOT_IN_WORK_ORDER') {
                    // Show High-Priority Warning Modal
                    document.getElementById('wrongPartTitle').innerText = 'PERINGATAN SALAH BARANG!';
                    document.getElementById('wrongPartMessage').innerText = res.message;
                    document.getElementById('wrongPartNumber').innerText = res.scanned_part.part_number;
                    document.getElementById('wrongPartName').innerText = res.scanned_part.name;
                    const wrongModal = new bootstrap.Modal(document.getElementById('wrongPartModal'));
                    wrongModal.show();
                } else {
                    alert(res.message);
                }
                return;
            }

            // SUCCESS! Item matches Work Order!
            playSuccessBeep();

            // Update row in table
            const item = res.item;
            const badge = document.getElementById(`item-verified-${item.id}`);
            const statusCell = document.getElementById(`item-status-${item.id}`);
            const row = document.getElementById(`item-row-${item.id}`);

            if (badge) {
                badge.innerText = `${item.verified_quantity} / ${item.quantity}`;
                if (item.is_verified) {
                    badge.className = 'badge bg-success fs-6';
                    statusCell.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>Picked</span>';
                    row.className = 'table-success bg-opacity-25';
                }
            }

            // Flash row effect
            if (row) {
                row.style.transition = 'background-color 0.3s ease';
                row.style.backgroundColor = '#bbf7d0';
                setTimeout(() => {
                    row.style.backgroundColor = '';
                }, 800);
            }

            // Update Progress
            updateDispatchProgressBar(res.progress);

            appendSessionActivity(item.name, item.part_number, '1 Pcs (Verified)', 'VERIFY_WO', '-', `SPK Terverifikasi (${item.verified_quantity}/${item.quantity})`);

            if (res.all_complete) {
                setTimeout(() => {
                    alert("SELAMAT! Semua suku cadang pada Work Order ini telah lengkap diverifikasi & siap diserahkan ke teknisi.");
                }, 300);
            }

        } catch (err) {
            console.error("Verify dispatch error", err);
            playErrorBuzzer();
            alert("Terjadi kesalahan jaringan saat verifikasi barang.");
        }
    }

    // Complete all verification at once
    async function completeAllVerification() {
        const woId = document.getElementById('dispatchWoSelect').value;
        if (!woId) return;

        if (!confirm("Konfirmasi verifikasi dan pengeluaran seluruh suku cadang untuk Work Order ini?")) {
            return;
        }

        try {
            const res = await fetch(`/scanner/work-orders/${woId}/complete-verification`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                playSuccessBeep();
                alert(data.message);
                loadWorkOrderDetails();
            }
        } catch (e) {
            alert("Gagal menyelesaikan verifikasi.");
        }
    }

    // Manual input search handler
    function handleManualSearch() {
        const input = document.getElementById('barcodeInput');
        const code = input.value.trim();
        if (code) {
            processBarcodeCode(code);
            input.value = '';
            input.focus();
        }
    }

    // Reset scan view
    function resetScanResult(mode) {
        currentPartData = null;
        if (mode === 'opname') {
            document.getElementById('opnameDetailArea').classList.add('d-none');
            document.getElementById('opnameEmptyState').classList.remove('d-none');
        } else if (mode === 'stock_in') {
            document.getElementById('stockInDetailArea').classList.add('d-none');
            document.getElementById('stockInEmptyState').classList.remove('d-none');
        } else if (mode === 'stock_out') {
            document.getElementById('stockOutDetailArea').classList.add('d-none');
            document.getElementById('stockOutEmptyState').classList.remove('d-none');
        }
        document.getElementById('barcodeInput').value = '';
        document.getElementById('barcodeInput').focus();
    }

    // Add activity to session table
    let sessionCount = 0;
    function appendSessionActivity(partName, partNumber, changeText, type, afterStock, notes) {
        sessionCount++;
        document.getElementById('sessionCountBadge').innerText = `${sessionCount} Aktivitas`;

        const tbody = document.getElementById('sessionActivityTbody');
        const tr = document.createElement('tr');
        tr.className = 'small table-light';

        let badgeClass = 'bg-primary-subtle text-primary';
        if (type === 'IN') badgeClass = 'bg-success-subtle text-success';
        if (type === 'OUT') badgeClass = 'bg-danger-subtle text-danger';
        if (type === 'VERIFY_WO') badgeClass = 'bg-info-subtle text-info';

        const now = new Date().toLocaleTimeString('id-ID');

        tr.innerHTML = `
            <td class="text-muted font-monospace">${now}</td>
            <td>
                <span class="fw-semibold">${partName}</span>
                <small class="text-muted d-block font-monospace">${partNumber}</small>
            </td>
            <td class="text-center">
                <span class="badge ${badgeClass}">${changeText}</span>
            </td>
            <td class="text-muted small">${notes || '-'}</td>
        `;

        if (tbody.firstChild) {
            tbody.insertBefore(tr, tbody.firstChild);
        } else {
            tbody.appendChild(tr);
        }
    }

    // Setup Keyboard listener for Barcode Scanner Gun
    document.addEventListener('DOMContentLoaded', function() {
        const barcodeInput = document.getElementById('barcodeInput');
        if (barcodeInput) {
            barcodeInput.focus();
            barcodeInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleManualSearch();
                }
            });
        }

        // Check if preselected work order has link
        const woSelect = document.getElementById('dispatchWoSelect');
        if (woSelect && woSelect.value) {
            updatePrintSlipLink(woSelect.value);
            loadWorkOrderDetails();
        }
    });

    // Cleanup scanner when leaving page
    window.addEventListener('beforeunload', function() {
        if (html5QrCode && isCameraRunning) {
            html5QrCode.stop();
        }
    });
</script>

<style>
@keyframes scanAnim {
    0% { top: 15%; opacity: 0.8; }
    50% { top: 85%; opacity: 1; }
    100% { top: 15%; opacity: 0.8; }
}
#reader video {
    object-fit: cover !important;
    border-radius: 6px;
    width: 100% !important;
    height: 100% !important;
}
</style>
@endpush
