@extends('layouts.app')

@section('title', 'Buat Faktur Pengembalian Barang ke Supplier')

@section('header')
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('supplier-returns.index') }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-1 text-dark">Terbitkan Faktur Pengembalian Barang ke Supplier</h4>
        <p class="text-muted small mb-0">Catat klaim retur barang rusak/cacat berdasarkan batch masuk dan kontak sales yang bertanggung jawab.</p>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('supplier-returns.store') }}" method="POST" x-data="supplierReturnForm({
    initialCost: {{ $selectedMovement ? (float)$selectedMovement->cost_price : ($selectedPart ? (float)$selectedPart->cost_price : 0) }},
    initialQty: 1,
    initialSupplierId: '{{ $selectedMovement ? $selectedMovement->supplier_id : ($selectedPart ? $selectedPart->supplier_id : '') }}',
    initialSalesId: '{{ $selectedMovement ? $selectedMovement->supplier_sales_id : '' }}',
    initialBatch: '{{ $selectedMovement ? addslashes($selectedMovement->batch_reference ?? '') : '' }}'
})">
    @csrf

    @if($selectedMovement)
        <input type="hidden" name="stock_movement_id" value="{{ $selectedMovement->id }}">
    @endif

    <div class="row g-4">
        
        <div class="col-12 col-lg-8">
            
            <!-- 1. IDENTIFIKASI BARANG & BATCH -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-danger"></i> 1. Rincian Sparepart & Batch Pembelian Masuk
                    </h6>
                </div>
                <div class="card-body p-4">
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih Sparepart yang Rusak / Cacat <span class="text-danger">*</span></label>
                        <select name="part_id" class="form-select @error('part_id') is-invalid @enderror" required onchange="if(this.value) window.location.href='{{ route('supplier-returns.create') }}?part_id=' + this.value">
                            <option value="">-- Pilih Sparepart --</option>
                            @foreach($parts as $p)
                                <option value="{{ $p->id }}" {{ ($selectedPart && $selectedPart->id === $p->id) ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->part_number }}) - Stok: {{ $p->stock }} {{ $p->unit }} &bull; HPP: Rp {{ number_format($p->cost_price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        @error('part_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($selectedPart)
                        <div class="p-3 bg-light rounded border mb-3">
                            <div class="row g-2 small">
                                <div class="col-6 col-md-3">
                                    <span class="text-muted d-block">Merk / Brand:</span>
                                    <strong class="text-dark">{{ $selectedPart->brand }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <span class="text-muted d-block">Kategori:</span>
                                    <span class="badge bg-secondary">{{ $selectedPart->category }}</span>
                                </div>
                                <div class="col-6 col-md-3">
                                    <span class="text-muted d-block">Stok Gudang:</span>
                                    <strong class="text-primary">{{ $selectedPart->stock }} {{ $selectedPart->unit }}</strong>
                                </div>
                                <div class="col-6 col-md-3">
                                    <span class="text-muted d-block">HPP Aktif:</span>
                                    <strong class="text-dark">Rp {{ number_format($selectedPart->cost_price, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- SUPPLIER & SALES PENANGGUNG JAWAB BATCH INI -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Supplier / Distributor Asal <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplierSelect" class="form-select" required onchange="onSupplierChanged(this.value)">
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ (($selectedMovement && $selectedMovement->supplier_id == $sup->id) || ($selectedPart && $selectedPart->supplier_id == $sup->id)) ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-primary">
                                <i class="bi bi-person-badge me-1"></i> Kontak Sales Penanggung Jawab Batch Ini
                            </label>
                            <select name="supplier_sales_id" id="salesSelect" class="form-select">
                                <option value="">-- Pilih Sales Representatif --</option>
                                <!-- Populated dynamically by JS -->
                            </select>
                            <div class="form-text small text-muted">Sales yang membawakan batch ini saat pengiriman ke bengkel.</div>
                        </div>
                    </div>

                    <!-- BATCH REFERENCE & HARGA MODAL -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">No. Batch / Surat Jalan Masuk</label>
                            <input type="text" name="batch_reference" class="form-control" placeholder="Contoh: SJ-2026-081" x-model="batchReference">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Harga Beli Batch (Rp) <span class="text-danger">*</span></label>
                            <input type="number" step="any" min="0" name="cost_price" class="form-control fw-bold" required x-model.number="costPrice" @input="recalculate()">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-danger">Jumlah Rusak / Diretur <span class="text-danger">*</span></label>
                            <input type="number" step="any" min="0.01" name="quantity" class="form-control fw-bold text-danger fs-5" required x-model.number="quantity" @input="recalculate()">
                            <div class="form-text small text-muted">Stok fisik gudang akan otomatis dikurangi.</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 2. ALASAN KERUSAKAN & METODE PENYELESAIAN -->
            <div class="card shadow-sm border-0 mb-4 border-start border-4 border-danger">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> 2. Alasan Kerusakan & Metode Penggantian
                    </h6>
                </div>
                <div class="card-body p-4">

                    <!-- Opsi Metode Penyelesaian -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Metode Penyelesaian Retur <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="settlement_type" id="typeReplacement" value="REPLACEMENT" checked>
                                <label class="btn btn-outline-primary w-100 p-3 text-start" for="typeReplacement">
                                    <div class="fw-bold"><i class="bi bi-arrow-repeat me-1"></i> Tukar Barang Baru (Replacement)</div>
                                    <small class="text-muted d-block mt-1">Supplier/sales akan mengganti dengan unit suku cadang baru dalam kondisi normal.</small>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="settlement_type" id="typeRefund" value="REFUND">
                                <label class="btn btn-outline-success w-100 p-3 text-start" for="typeRefund">
                                    <div class="fw-bold"><i class="bi bi-cash-stack me-1"></i> Potong Tagihan / Uang Kembali (Refund)</div>
                                    <small class="text-muted d-block mt-1">Nilai retur dipotongkan dari faktur hutang bengkel atau ditransfer kembali.</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Alasan Kerusakan -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Kerusakan / Cacat Barang <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="reason" 
                               class="form-control" 
                               list="defectReasons" 
                               placeholder="Contoh: Cacat pabrik saat unboxing, bocor oli, ukuran tidak presisi..." 
                               required>
                        <datalist id="defectReasons">
                            <option value="Cacat produksi pabrik (Defect Manufacturer)">
                            <option value="Bocor oli / cairan sebelum terpasang">
                            <option value="Ukuran drat / pin tidak presisi (Salah Cetak)">
                            <option value="Patah / retak di dalam kemasan distributor">
                            <option value="Salah kirim spesifikasi oleh sales supplier">
                            <option value="Bunyi kasar / gagal fungsi saat running test">
                        </datalist>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Catatan Kondisi Fisik Tambahan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan detail nomor seri yang tertera, kondisi kemasan, tanggal penerimaan..."></textarea>
                    </div>

                </div>
            </div>

        </div>

        <!-- KOLOM KANAN: RINGKASAN FAKTUR RETUR & TOMBOL SUBMIT -->
        <div class="col-12 col-lg-4">
            
            <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
                <div class="card-header bg-danger text-white py-3">
                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-medical"></i> Ringkasan Nilai Retur
                    </h6>
                </div>
                <div class="card-body p-4">
                    
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Jumlah Barang:</span>
                        <span class="fw-bold text-danger fs-6" x-text="quantity + ' {{ $selectedPart->unit ?? 'Unit' }}'"></span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Harga Beli Satuan:</span>
                        <span class="fw-semibold text-dark" x-text="formatRupiah(costPrice)"></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-3 my-2 border-top border-bottom border-2">
                        <span class="fw-bold fs-6 text-dark">TOTAL NILAI KLAIM:</span>
                        <span class="fw-bold fs-4 text-danger" x-text="formatRupiah(totalAmount)"></span>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-3 fw-bold fs-6 d-flex align-items-center justify-content-center gap-2 shadow-sm" :disabled="quantity <= 0 || costPrice <= 0">
                        <i class="bi bi-arrow-return-left fs-5"></i> Terbitkan Faktur Retur Resmi
                    </button>

                    <div class="mt-3 p-3 bg-light rounded text-muted small">
                        <i class="bi bi-info-circle text-primary me-1"></i> Setelah faktur retur terbit, Anda dapat langsung mencetak tanda terima retur resmi serta mengirimkan notifikasi instan via WhatsApp ke sales penanggung jawab.
                    </div>

                </div>
            </div>

        </div>

    </div>
</form>

<script>
    const suppliersData = @json($suppliers);

    function onSupplierChanged(supplierId, targetSalesId = null) {
        const salesSelect = document.getElementById('salesSelect');
        salesSelect.innerHTML = '<option value="">-- Pilih Sales Penanggung Jawab --</option>';

        if (!supplierId) return;

        const sup = suppliersData.find(s => s.id == supplierId);
        if (sup && sup.sales && sup.sales.length > 0) {
            sup.sales.forEach(sale => {
                const opt = document.createElement('option');
                opt.value = sale.id;
                opt.textContent = sale.name + (sale.phone ? ' (' + sale.phone + ')' : '') + (sale.area ? ' - ' + sale.area : '');
                if (targetSalesId && sale.id == targetSalesId) {
                    opt.selected = true;
                }
                salesSelect.appendChild(opt);
            });
        } else {
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = 'Tidak ada sales khusus (Hubungi kontak utama supplier)';
            salesSelect.appendChild(opt);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const supSelect = document.getElementById('supplierSelect');
        if (supSelect && supSelect.value) {
            onSupplierChanged(supSelect.value, '{{ $selectedMovement ? $selectedMovement->supplier_sales_id : '' }}');
        }
    });

    function supplierReturnForm(config) {
        return {
            costPrice: config.initialCost || 0,
            quantity: config.initialQty || 1,
            batchReference: config.initialBatch || '',
            totalAmount: 0,

            init() {
                this.recalculate();
            },

            recalculate() {
                this.totalAmount = Math.round((parseFloat(this.costPrice) || 0) * (parseFloat(this.quantity) || 0));
            },

            formatRupiah(num) {
                return 'Rp ' + Math.round(num || 0).toLocaleString('id-ID');
            }
        }
    }
</script>
@endsection
