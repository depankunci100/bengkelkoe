@extends('layouts.app')

@section('title', 'Kasir Pembayaran POS')

@section('header')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('payments.index') }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0 text-dark">Kasir Pembayaran</h4>
        <small class="text-muted">Proses penerimaan pembayaran tunai, QRIS, transfer bank, atau kartu debit.</small>
    </div>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">

        @if(!$invoice)
            <!-- Pemilih Faktur / Invoice Tagihan -->
            <x-card title="Pilih Tagihan / Invoice yang Akan Dibayar" icon="receipt" class="mb-4">
                @if($unpaidInvoices->isEmpty())
                    <div class="text-center py-4 text-success">
                        <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                        <h6 class="fw-bold">Tidak Ada Tagihan Belum Lunas</h6>
                        <small class="text-muted">Semua faktur perbaikan saat ini sudah terbayar lunas.</small>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($unpaidInvoices as $unp)
                            <a href="{{ route('payments.create', ['invoice_id' => $unp->id]) }}" class="list-group-item list-group-item-action p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-dark d-block">{{ $unp->invoice_number }}</span>
                                    <small class="text-muted">{{ $unp->customer->name }} &bull; {{ $unp->workOrder->vehicle->plate_number }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-danger d-block">Sisa: Rp {{ number_format($unp->balance_due, 0, ',', '.') }}</span>
                                    <span class="badge bg-light text-muted border">Total: Rp {{ number_format($unp->grand_total, 0, ',', '.') }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-card>
        @else
            <!-- FORM KASIR PEMBAYARAN SESUAI SECTION 22 MASTER PROMPT -->
            <div class="card shadow border-0 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-cash-stack"></i> PEMBAYARAN KASIR
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Rincian Invoice & Customer -->
                    <div class="bg-light p-3 rounded border mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">No. Invoice:</span>
                            <span class="fw-bold font-monospace text-dark">{{ $invoice->invoice_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Customer:</span>
                            <strong class="text-dark">{{ $invoice->customer->name }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Kendaraan:</span>
                            <span class="badge bg-dark">{{ $invoice->workOrder->vehicle->plate_number }}</span>
                        </div>
                    </div>

                    <!-- Ringkasan Angka (Total, Dibayar, Sisa) -->
                    <div class="p-3 bg-white border rounded mb-4">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-semibold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($invoice->discount > 0)
                            <div class="d-flex justify-content-between py-1 text-success small">
                                <span>
                                    Potongan Diskon:
                                    @if($invoice->discount_type === 'PERCENT' && $invoice->discount_percent > 0)
                                        ({{ (float)$invoice->discount_percent }}%)
                                    @endif
                                    @if($invoice->discount_reason)
                                        <small class="text-muted d-block" style="font-size: 0.70rem;">({{ $invoice->discount_reason }})</small>
                                    @endif
                                </span>
                                <span class="fw-bold">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Grand Total Tagihan:</span>
                            <span class="fw-semibold text-primary">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">Sudah Dibayar:</span>
                            <span class="fw-semibold text-success">Rp {{ number_format($invoice->amount_paid, 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between py-1">
                            <span class="fw-bold fs-5 text-dark">Sisa Tagihan:</span>
                            <span class="fw-bold fs-5 text-danger">Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <form action="{{ route('payments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">

                        <!-- Pilihan Metode Pembayaran -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Metode Pembayaran</label>
                            <select name="payment_method" class="form-select form-select-lg" required>
                                <option value="CASH">💵 CASH (Tunai)</option>
                                <option value="BANK_TRANSFER">🏦 BANK TRANSFER (BCA / Mandiri / BRI)</option>
                                <option value="QRIS">📱 QRIS (BCA / Gopay / OVO)</option>
                                <option value="DEBIT_CREDIT">💳 KARTU DEBIT / KREDIT (EDC)</option>
                            </select>
                        </div>

                        <!-- Jumlah Nominal Bayar -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Jumlah Bayar (Rp)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text fw-bold bg-light">Rp</span>
                                <input type="number" name="amount" class="form-control fw-bold fs-4 text-primary" value="{{ (int) $invoice->balance_due }}" min="1" max="{{ (int) $invoice->balance_due }}" required>
                            </div>
                            <div class="form-text text-muted">Bisa dibayar lunas atau cicilan / DP sebagian.</div>
                        </div>

                        <!-- Referensi Pembayaran -->
                        <div class="mb-4">
                            <label class="form-label small text-muted">No. Referensi Transaksi / Catatan (Opsional)</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Nomor struk EDC / ID Transaksi Bank...">
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 py-3 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                            <i class="bi bi-check2-circle fs-4"></i> PROSES PEMBAYARAN
                        </button>
                    </form>

                </div>
            </div>
        @endif

    </div>
</div>
@endsection
