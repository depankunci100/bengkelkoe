@extends('layouts.app')

@section('title', 'Terbitkan Invoice & Atur Diskon')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-1 text-dark">Terbitkan Faktur Invoice</h4>
            <p class="text-muted small mb-0">Tentukan rincian tagihan, terapkan potongan diskon (Nominal / Persen), dan terbitkan invoice resmi.</p>
        </div>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf

    <div class="row g-4" x-data="invoiceDiscountCalculator({
        subtotal: {{ $selectedWorkOrder ? (float)$selectedWorkOrder->subtotal : 0 }},
        initialType: 'FIXED',
        initialValue: {{ $selectedWorkOrder && $selectedWorkOrder->discount > 0 ? (float)$selectedWorkOrder->discount : 0 }},
        includeTax: true
    })">
        
        <!-- KOLOM KIRI: PILIH WORK ORDER & PENGATURAN DISKON -->
        <div class="col-12 col-lg-8">

            <!-- 1. PILIH WORK ORDER -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-text text-primary"></i> 1. Pilih Work Order (Surat Perintah Kerja)
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Work Order yang Akan Ditagihkan <span class="text-danger">*</span></label>
                        <select name="work_order_id" class="form-select @error('work_order_id') is-invalid @enderror" required onchange="if(this.value) window.location.href='{{ route('invoices.create') }}?work_order_id=' + this.value">
                            <option value="">-- Pilih Work Order Siap Tagih --</option>
                            @foreach($workOrders as $itemWo)
                                <option value="{{ $itemWo->id }}" {{ ($selectedWorkOrder && $selectedWorkOrder->id === $itemWo->id) ? 'selected' : '' }}>
                                    {{ $itemWo->wo_number }} &bull; {{ $itemWo->customer->name }} ({{ $itemWo->vehicle->plate_number }}) - Estimasi: Rp {{ number_format($itemWo->subtotal, 0, ',', '.') }} [{{ $itemWo->status }}]
                                </option>
                            @endforeach
                        </select>
                        @error('work_order_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($selectedWorkOrder)
                        <div class="p-3 bg-light rounded border">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Customer</small>
                                    <span class="fw-bold text-dark">{{ $selectedWorkOrder->customer->name }}</span>
                                    <div class="small text-muted">{{ $selectedWorkOrder->customer->phone }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Kendaraan</small>
                                    <span class="badge bg-dark fs-7">{{ $selectedWorkOrder->vehicle->plate_number }}</span>
                                    <div class="small text-muted">{{ $selectedWorkOrder->vehicle->brand }} {{ $selectedWorkOrder->vehicle->model }}</div>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Status Pengerjaan</small>
                                    <x-status-badge :status="$selectedWorkOrder->status" />
                                </div>
                            </div>
                        </div>

                        <!-- RINCIAN JASA & PART DARI WO -->
                        <div class="mt-4">
                            <h6 class="fw-bold text-dark mb-2 small text-uppercase"><i class="bi bi-list-check"></i> Rincian Item Pekerjaan & Suku Cadang:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Tipe</th>
                                            <th>Deskripsi Item</th>
                                            <th class="text-center" style="width: 70px;">Qty</th>
                                            <th class="text-end" style="width: 130px;">Harga</th>
                                            <th class="text-end" style="width: 140px;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($selectedWorkOrder->items as $it)
                                            <tr>
                                                <td>
                                                    <span class="badge {{ $it->type === 'SERVICE' ? 'bg-info-subtle text-info border' : 'bg-primary-subtle text-primary border' }} fs-7">
                                                        {{ $it->type }}
                                                    </span>
                                                </td>
                                                <td class="small fw-semibold text-dark">{{ $it->item_name }}</td>
                                                <td class="text-center small">{{ (float) $it->quantity }}</td>
                                                <td class="text-end small">Rp {{ number_format($it->unit_price, 0, ',', '.') }}</td>
                                                <td class="text-end small fw-bold">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted small py-2">Belum ada rincian item pada Work Order ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="4" class="text-end small text-uppercase">Total Subtotal Kotor:</th>
                                            <th class="text-end small fw-bold text-primary">Rp {{ number_format($selectedWorkOrder->subtotal, 0, ',', '.') }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info py-2 small mb-0">
                            <i class="bi bi-info-circle me-1"></i> Silakan pilih Work Order terlebih dahulu untuk memuat rincian biaya servis.
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. FITUR PENGATURAN DISKON -->
            <div class="card shadow-sm border-0 mb-4 border-start border-4 border-success">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2 text-success">
                        <i class="bi bi-percent fs-5"></i> 2. Pengaturan Diskon & Potongan Harga
                    </h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Diskon Transaksi</span>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Pilihan Tipe Diskon (Fixed Rp vs Percent %) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Metode / Jenis Diskon</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="discount_type" id="discTypeFixed" value="FIXED" x-model="discountType" @change="recalculate()">
                            <label class="btn btn-outline-success py-2 fw-semibold" for="discTypeFixed">
                                <i class="bi bi-cash-stack me-1"></i> Nominal Tetap (Rupiah / Rp)
                            </label>

                            <input type="radio" class="btn-check" name="discount_type" id="discTypePercent" value="PERCENT" x-model="discountType" @change="recalculate()">
                            <label class="btn btn-outline-success py-2 fw-semibold" for="discTypePercent">
                                <i class="bi bi-percent me-1"></i> Persentase ( % )
                            </label>
                        </div>
                    </div>

                    <!-- Input Nilai Diskon -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">
                                <span x-show="discountType === 'FIXED'">Nominal Potongan Diskon (Rp)</span>
                                <span x-show="discountType === 'PERCENT'">Persentase Diskon (0 - 100%)</span>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text fw-bold bg-light" x-text="discountType === 'FIXED' ? 'Rp' : '%'"></span>
                                <input type="number" 
                                       step="any" 
                                       min="0" 
                                       :max="discountType === 'PERCENT' ? 100 : subtotal" 
                                       name="discount_value" 
                                       id="discountValueInput"
                                       class="form-control fw-bold fs-5 text-success" 
                                       placeholder="0" 
                                       x-model.number="discountValue" 
                                       @input="recalculate()">
                            </div>
                            <small class="text-muted" x-show="discountType === 'PERCENT'">
                                Potongan setara: <strong class="text-success" x-text="formatRupiah(calculatedDiscountAmount)"></strong>
                            </small>
                            <small class="text-muted" x-show="discountType === 'FIXED' && subtotal > 0">
                                Setara potongan: <strong class="text-success" x-text="calculatedDiscountPercent.toFixed(1) + '%'"></strong>
                            </small>
                        </div>

                        <!-- Rekomendasi Diskon Cepat (Quick Preset Buttons) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pilih Cepat (Preset):</label>
                            
                            <!-- Preset untuk PERCENT -->
                            <div class="d-flex flex-wrap gap-1" x-show="discountType === 'PERCENT'">
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setPreset(0)">0%</button>
                                <button type="button" class="btn btn-sm btn-outline-success" @click="setPreset(5)">5%</button>
                                <button type="button" class="btn btn-sm btn-outline-success" @click="setPreset(10)">10%</button>
                                <button type="button" class="btn btn-sm btn-outline-success" @click="setPreset(15)">15%</button>
                                <button type="button" class="btn btn-sm btn-outline-success" @click="setPreset(20)">20%</button>
                                <button type="button" class="btn btn-sm btn-outline-success" @click="setPreset(25)">25%</button>
                            </div>

                            <!-- Preset untuk FIXED Rp -->
                            <div class="d-flex flex-wrap gap-1" x-show="discountType === 'FIXED'">
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="setPreset(0)">Rp 0</button>
                                <button type="button" class="btn btn-sm btn-outline-success" @click="setPreset(25000)">25 rb</button>
                                <button type="button" class="btn btn-sm btn-outline-success" @click="setPreset(50000)">50 rb</button>
                                <button type="button" class="btn btn-sm btn-outline-success" @click="setPreset(100000)">100 rb</button>
                                <button type="button" class="btn btn-sm btn-outline-success" @click="setPreset(200000)">200 rb</button>
                            </div>
                        </div>
                    </div>

                    <!-- Alasan / Kategori Diskon -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan / Kategori Diskon (Opsional)</label>
                        <input type="text" 
                               name="discount_reason" 
                               class="form-control" 
                               list="discountReasonsList" 
                               placeholder="Contoh: Promo Member Setia, Diskon Rekanan Sales, Hari Spesial..." 
                               x-model="discountReason">
                        <datalist id="discountReasonsList">
                            <option value="Promo Loyalitas Pelanggan (Member)">
                            <option value="Diskon Rujukan Rekanan Sales">
                            <option value="Promo Servis Rutin Bulanan">
                            <option value="Diskon Khusus Pemilik Bengkel / Owner">
                            <option value="Kompensasi Waktu Pengerjaan">
                            <option value="Potongan Negosiasi Kasir">
                        </datalist>
                        <div class="d-flex gap-1 mt-2 flex-wrap">
                            <span class="badge bg-light text-dark border cursor-pointer" role="button" @click="discountReason = 'Promo Member Setia'">Member Setia</span>
                            <span class="badge bg-light text-dark border cursor-pointer" role="button" @click="discountReason = 'Diskon Rekanan Sales'">Rekanan Sales</span>
                            <span class="badge bg-light text-dark border cursor-pointer" role="button" @click="discountReason = 'Promo Servis Berkala'">Promo Servis</span>
                            <span class="badge bg-light text-dark border cursor-pointer" role="button" @click="discountReason = 'Diskon Khusus Owner'">Khusus Owner</span>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Pengaturan Pajak PPN & Catatan -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="include_tax" id="includeTaxCheck" value="1" x-model="includeTax" @change="recalculate()">
                        <label class="form-check-label fw-semibold text-dark" for="includeTaxCheck">
                            Kenakan Pajak Pertambahan Nilai (PPN 11%)
                        </label>
                        <small class="text-muted d-block">PPN dihitung dari Dasar Pengenaan Pajak (Subtotal setelah dikurangi diskon).</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Catatan Faktur (Ditampilkan pada Struk/Invoice)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan opsional untuk dicetak di invoice..."></textarea>
                    </div>

                </div>
            </div>

        </div>

        <!-- KOLOM KANAN: RINGKASAN KALKULASI INTERAKTIF & TOMBOL TERBITKAN -->
        <div class="col-12 col-lg-4">
            
            <div class="card shadow-sm border-0 sticky-top" style="top: 80px;">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-calculator"></i> Ringkasan Total Tagihan
                    </h6>
                </div>
                <div class="card-body p-4">
                    
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Subtotal Kotor:</span>
                        <span class="fw-bold text-dark" x-text="formatRupiah(subtotal)"></span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom" :class="calculatedDiscountAmount > 0 ? 'bg-success-subtle px-2 rounded my-1' : ''">
                        <div>
                            <span class="fw-semibold" :class="calculatedDiscountAmount > 0 ? 'text-success' : 'text-muted'">Potongan Diskon:</span>
                            <template x-if="calculatedDiscountAmount > 0 && discountReason">
                                <small class="text-muted d-block" style="font-size: 0.72rem;" x-text="'(' + discountReason + ')'"></small>
                            </template>
                        </div>
                        <span class="fw-bold" :class="calculatedDiscountAmount > 0 ? 'text-success' : 'text-muted'" x-text="'- ' + formatRupiah(calculatedDiscountAmount)"></span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom small">
                        <span class="text-muted">Dasar Pengenaan Pajak (DPP):</span>
                        <span class="fw-semibold text-dark" x-text="formatRupiah(taxableBase)"></span>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Pajak PPN (11%):</span>
                        <span class="fw-semibold text-dark" x-text="formatRupiah(taxAmount)"></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-3 my-2 border-top border-bottom border-2">
                        <span class="fw-bold fs-6 text-dark">GRAND TOTAL:</span>
                        <span class="fw-bold fs-4 text-primary" x-text="formatRupiah(grandTotal)"></span>
                    </div>

                    <!-- Tombol Submit Form -->
                    <button type="submit" 
                            class="btn btn-primary w-100 py-3 fw-bold fs-6 d-flex align-items-center justify-content-center gap-2 shadow-sm"
                            :disabled="subtotal <= 0">
                        <i class="bi bi-receipt-cutoff fs-5"></i> Terbitkan Invoice & Faktur
                    </button>

                    <div class="mt-3 p-3 bg-light rounded text-muted small">
                        <i class="bi bi-shield-check text-success me-1"></i> Setelah invoice terbit, kasir dapat langsung menerima pembayaran (Cash, Transfer, QRIS) dan mencetak faktur A4 atau struk.
                    </div>

                </div>
            </div>

        </div>

    </div>
</form>
@endsection

@push('scripts')
<script>
function invoiceDiscountCalculator(config) {
    return {
        subtotal: config.subtotal || 0,
        discountType: config.initialType || 'FIXED',
        discountValue: config.initialValue || 0,
        discountReason: '',
        includeTax: config.includeTax !== false,

        calculatedDiscountAmount: 0,
        calculatedDiscountPercent: 0,
        taxableBase: 0,
        taxAmount: 0,
        grandTotal: 0,

        init() {
            this.recalculate();
        },

        setPreset(val) {
            this.discountValue = val;
            this.recalculate();
        },

        recalculate() {
            const sub = parseFloat(this.subtotal) || 0;
            let val = parseFloat(this.discountValue) || 0;
            if (val < 0) val = 0;

            if (this.discountType === 'PERCENT') {
                if (val > 100) val = 100;
                this.calculatedDiscountPercent = val;
                this.calculatedDiscountAmount = Math.round((sub * val) / 100);
            } else {
                if (val > sub) val = sub;
                this.calculatedDiscountAmount = val;
                this.calculatedDiscountPercent = sub > 0 ? (val / sub) * 100 : 0;
            }

            this.taxableBase = Math.max(0, sub - this.calculatedDiscountAmount);
            this.taxAmount = this.includeTax ? Math.round(this.taxableBase * 0.11) : 0;
            this.grandTotal = this.taxableBase + this.taxAmount;
        },

        formatRupiah(number) {
            return 'Rp ' + Math.round(number || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
