@extends('layouts.app')

@section('title', 'Faktur Invoice - ' . $invoice->invoice_number)

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">{{ $invoice->invoice_number }}</h4>
                <x-status-badge :status="$invoice->payment_status" class="fs-6 px-3 py-1" />
            </div>
            <small class="text-muted">Tanggal: {{ $invoice->created_at->translatedFormat('d F Y') }} &bull; No. Work Order: {{ $invoice->workOrder->wo_number }}</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-light border text-dark d-inline-flex align-items-center gap-2">
            <i class="bi bi-printer-fill"></i> Cetak Faktur PDF/Print
        </a>
        @if($invoice->payment_status !== 'PAID')
            <button type="button" class="btn btn-outline-success d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#editDiscountModal">
                <i class="bi bi-tag-fill"></i> Atur / Ubah Diskon
            </button>
            <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success d-inline-flex align-items-center gap-2">
                <i class="bi bi-cash-stack"></i> Proses Pembayaran
            </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                
                <!-- Info Header Faktur -->
                <div class="row g-3 mb-4 border-bottom pb-4">
                    <div class="col-6">
                        <span class="text-muted small text-uppercase fw-bold d-block">Tagihan Kepada:</span>
                        <h6 class="fw-bold text-dark mb-1">{{ $invoice->customer->name }}</h6>
                        <small class="text-muted d-block">{{ $invoice->customer->phone }}</small>
                        <small class="text-muted">{{ $invoice->customer->address ?? '-' }}</small>
                    </div>
                    <div class="col-6 text-end">
                        <span class="text-muted small text-uppercase fw-bold d-block">Kendaraan Diservis:</span>
                        <span class="badge bg-dark fs-6 px-2 py-1 mb-1">{{ $invoice->workOrder->vehicle->plate_number }}</span>
                        <div class="fw-bold text-dark small">{{ $invoice->workOrder->vehicle->brand }} {{ $invoice->workOrder->vehicle->model }}</div>
                    </div>
                </div>

                <!-- Rincian Pekerjaan & Sparepart -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">Rincian Transaksi Servis</h6>
                    @if($invoice->payment_status !== 'PAID')
                        <button type="button" class="btn btn-sm btn-outline-success py-1" data-bs-toggle="modal" data-bs-target="#editDiscountModal">
                            <i class="bi bi-percent me-1"></i> {{ $invoice->discount > 0 ? 'Edit Diskon' : '+ Tambah Diskon' }}
                        </button>
                    @endif
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Deskripsi Item</th>
                                <th class="text-center" style="width: 80px;">Qty</th>
                                <th class="text-end" style="width: 140px;">Harga Satuan</th>
                                <th class="text-end" style="width: 140px;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->workOrder->items as $it)
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $it->item_name }}</span>
                                        <small class="text-muted ms-2">({{ $it->type }})</small>
                                    </td>
                                    <td class="text-center">{{ (float) $it->quantity }}</td>
                                    <td class="text-end small">Rp {{ number_format($it->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Subtotal:</th>
                                <td class="text-end fw-semibold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @if($invoice->discount > 0)
                                <tr class="table-success-subtle">
                                    <th colspan="3" class="text-end text-success">
                                        <i class="bi bi-tag-fill me-1"></i> Potongan Diskon
                                        @if($invoice->discount_type === 'PERCENT' && $invoice->discount_percent > 0)
                                            <span class="badge bg-success ms-1">{{ (float)$invoice->discount_percent }}%</span>
                                        @endif
                                        @if($invoice->discount_reason)
                                            <small class="text-muted d-block fw-normal fst-italic">Keterangan: {{ $invoice->discount_reason }}</small>
                                        @endif
                                    </th>
                                    <td class="text-end text-success fw-bold">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th colspan="3" class="text-end">PPN (11%):</th>
                                <td class="text-end fw-semibold">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                            </tr>
                            <tr class="table-light">
                                <th colspan="3" class="text-end fs-6 text-dark">Grand Total Tagihan:</th>
                                <td class="text-end fs-6 fw-bold text-primary">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($invoice->notes)
                    <div class="p-3 bg-light rounded small text-muted border">
                        <strong>Catatan Faktur:</strong> {{ $invoice->notes }}
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Status Pelunasan & Riwayat Pembayaran -->
    <div class="col-12 col-lg-4 d-flex flex-column gap-4">
        
        <x-card title="Status Saldo Tagihan" icon="wallet2">
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Subtotal:</span>
                <span class="fw-semibold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($invoice->discount > 0)
                <div class="d-flex justify-content-between py-2 border-bottom text-success">
                    <span>
                        Potongan Diskon:
                        @if($invoice->discount_type === 'PERCENT' && $invoice->discount_percent > 0)
                            <small>({{ (float)$invoice->discount_percent }}%)</small>
                        @endif
                    </span>
                    <span class="fw-bold">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">PPN (11%):</span>
                <span class="fw-semibold">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="fw-bold text-dark">Total Tagihan:</span>
                <span class="fw-bold fs-6 text-primary">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Telah Dibayar:</span>
                <span class="fw-bold text-success">Rp {{ number_format($invoice->amount_paid, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between py-2">
                <span class="fw-bold text-dark">Sisa Tagihan (Balance):</span>
                <span class="fw-bold text-danger fs-5">Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</span>
            </div>

            @if($invoice->balance_due > 0)
                <div class="d-flex flex-column gap-2 mt-3">
                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success w-100 py-2 fw-semibold">
                        <i class="bi bi-cash-coin me-1"></i> Bayar Sisa Tagihan di Kasir
                    </a>
                    <button type="button" class="btn btn-outline-secondary w-100 py-2 small" data-bs-toggle="modal" data-bs-target="#editDiscountModal">
                        <i class="bi bi-tag me-1"></i> Atur / Sesuaikan Diskon
                    </button>
                </div>
            @else
                <div class="text-center p-2 bg-success-subtle text-success rounded mt-3 fw-bold small">
                    <i class="bi bi-check-circle-fill"></i> TAGIHAN SUDAH LUNAS
                </div>
            @endif
        </x-card>

        <x-card title="Riwayat Pembayaran Diterima" icon="receipt-cutoff">
            @if($invoice->payments->isEmpty())
                <p class="text-muted small text-center my-3">Belum ada pembayaran yang tercatat.</p>
            @else
                <div class="d-flex flex-column gap-2">
                    @foreach($invoice->payments as $p)
                        <div class="p-2 border rounded bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold font-monospace small">{{ $p->payment_number }}</span>
                                <span class="badge bg-success small">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                            </div>
                            <small class="text-muted d-block">
                                {{ $p->payment_method }} &bull; {{ $p->paid_at->format('d/m/Y H:i') }}
                            </small>
                            <small class="text-muted">Kasir: {{ $p->cashier->name ?? 'Kasir' }}</small>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

    </div>
</div>

<!-- MODAL KELOLA / UBAH DISKON INVOICE -->
@if($invoice->payment_status !== 'PAID')
<div class="modal fade" id="editDiscountModal" tabindex="-1" aria-labelledby="editDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('invoices.update-discount', $invoice->id) }}" method="POST" x-data="editDiscountCalculator({
                subtotal: {{ (float)$invoice->subtotal }},
                initialType: '{{ $invoice->discount_type ?? 'FIXED' }}',
                initialValue: {{ $invoice->discount_type === 'PERCENT' ? (float)($invoice->discount_percent ?? 0) : (float)($invoice->discount ?? 0) }},
                initialReason: '{{ addslashes($invoice->discount_reason ?? '') }}',
                includeTax: {{ (float)$invoice->tax > 0 ? 'true' : 'false' }},
                amountPaid: {{ (float)$invoice->amount_paid }}
            })">
                @csrf
                @method('PUT')

                <div class="modal-header bg-success text-white py-3">
                    <h6 class="modal-title fw-bold" id="editDiscountModalLabel">
                        <i class="bi bi-tag-fill me-1"></i> Atur & Sesuaikan Diskon Invoice
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="mb-3 p-2 bg-light rounded border d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Subtotal Kotor Transaksi:</span>
                        <strong class="text-dark" x-text="formatRupiah(subtotal)"></strong>
                    </div>

                    <!-- Jenis Diskon -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Jenis Potongan Diskon</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="discount_type" id="modalDiscFixed" value="FIXED" x-model="discountType" @change="recalculate()">
                            <label class="btn btn-outline-success py-2 btn-sm fw-semibold" for="modalDiscFixed">Nominal (Rp)</label>

                            <input type="radio" class="btn-check" name="discount_type" id="modalDiscPercent" value="PERCENT" x-model="discountType" @change="recalculate()">
                            <label class="btn btn-outline-success py-2 btn-sm fw-semibold" for="modalDiscPercent">Persen (%)</label>
                        </div>
                    </div>

                    <!-- Nilai Diskon -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            <span x-show="discountType === 'FIXED'">Nominal Diskon (Rp)</span>
                            <span x-show="discountType === 'PERCENT'">Persentase Diskon (%)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light" x-text="discountType === 'FIXED' ? 'Rp' : '%'"></span>
                            <input type="number" 
                                   step="any" 
                                   min="0" 
                                   name="discount_value" 
                                   class="form-control fw-bold text-success fs-5" 
                                   x-model.number="discountValue" 
                                   @input="recalculate()">
                        </div>
                        <small class="text-muted" x-show="discountType === 'PERCENT'">
                            Nilai potongan: <strong class="text-success" x-text="formatRupiah(calculatedDiscountAmount)"></strong>
                        </small>
                    </div>

                    <!-- Preset Cepat -->
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Preset Cepat:</label>
                        <div class="d-flex flex-wrap gap-1" x-show="discountType === 'PERCENT'">
                            <button type="button" class="btn btn-xs btn-outline-secondary" @click="setPreset(0)">0%</button>
                            <button type="button" class="btn btn-xs btn-outline-success" @click="setPreset(5)">5%</button>
                            <button type="button" class="btn btn-xs btn-outline-success" @click="setPreset(10)">10%</button>
                            <button type="button" class="btn btn-xs btn-outline-success" @click="setPreset(15)">15%</button>
                            <button type="button" class="btn btn-xs btn-outline-success" @click="setPreset(20)">20%</button>
                        </div>
                        <div class="d-flex flex-wrap gap-1" x-show="discountType === 'FIXED'">
                            <button type="button" class="btn btn-xs btn-outline-secondary" @click="setPreset(0)">Rp 0</button>
                            <button type="button" class="btn btn-xs btn-outline-success" @click="setPreset(25000)">25 rb</button>
                            <button type="button" class="btn btn-xs btn-outline-success" @click="setPreset(50000)">50 rb</button>
                            <button type="button" class="btn btn-xs btn-outline-success" @click="setPreset(100000)">100 rb</button>
                        </div>
                    </div>

                    <!-- Alasan Diskon -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alasan / Keterangan Diskon</label>
                        <input type="text" name="discount_reason" class="form-control form-control-sm" placeholder="Contoh: Promo Member, Diskon Rekanan..." x-model="discountReason">
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="include_tax" id="modalIncludeTax" value="1" x-model="includeTax" @change="recalculate()">
                        <label class="form-check-label small fw-semibold" for="modalIncludeTax">Kenakan PPN (11%)</label>
                    </div>

                    <!-- Preview Ringkasan Hasil Kalkulasi Baru -->
                    <div class="p-3 bg-light rounded border">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Potongan Diskon:</span>
                            <strong class="text-success" x-text="'- ' + formatRupiah(calculatedDiscountAmount)"></strong>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">PPN (11%):</span>
                            <span class="fw-semibold text-dark" x-text="formatRupiah(taxAmount)"></span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <span class="fw-bold text-dark">Grand Total Baru:</span>
                            <strong class="text-primary fs-6" x-text="formatRupiah(grandTotal)"></strong>
                        </div>
                        <div class="d-flex justify-content-between small pt-1" x-show="amountPaid > 0">
                            <span class="text-muted">Telah Dibayar (DP):</span>
                            <span class="text-success fw-bold" x-text="formatRupiah(amountPaid)"></span>
                        </div>
                        <div class="d-flex justify-content-between small pt-1" x-show="amountPaid > 0">
                            <span class="text-dark fw-bold">Sisa Saldo Baru:</span>
                            <strong class="text-danger" x-text="formatRupiah(Math.max(0, grandTotal - amountPaid))"></strong>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan Diskon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function editDiscountCalculator(config) {
    return {
        subtotal: config.subtotal || 0,
        discountType: config.initialType || 'FIXED',
        discountValue: config.initialValue || 0,
        discountReason: config.initialReason || '',
        includeTax: config.includeTax !== false,
        amountPaid: config.amountPaid || 0,

        calculatedDiscountAmount: 0,
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
                this.calculatedDiscountAmount = Math.round((sub * val) / 100);
            } else {
                if (val > sub) val = sub;
                this.calculatedDiscountAmount = val;
            }

            const taxableBase = Math.max(0, sub - this.calculatedDiscountAmount);
            this.taxAmount = this.includeTax ? Math.round(taxableBase * 0.11) : 0;
            this.grandTotal = taxableBase + this.taxAmount;
        },

        formatRupiah(num) {
            return 'Rp ' + Math.round(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
