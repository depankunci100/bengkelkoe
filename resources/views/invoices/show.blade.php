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
        <a href="{{ route('invoices.print', $invoice->id) }}" target="_blank" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-printer-fill"></i> Cetak Faktur PDF/Print
        </a>
        @if($invoice->payment_status !== 'PAID')
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
                <h6 class="fw-bold text-dark mb-3">Rincian Transaksi Servis</h6>
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
                                <tr>
                                    <th colspan="3" class="text-end text-success">Diskon:</th>
                                    <td class="text-end text-success fw-semibold">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
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

            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Status Pelunasan & Riwayat Pembayaran -->
    <div class="col-12 col-lg-4 d-flex flex-column gap-4">
        
        <x-card title="Status Saldo Tagihan" icon="wallet2">
            <div class="d-flex justify-content-between py-2 border-bottom">
                <span class="text-muted">Total Tagihan:</span>
                <span class="fw-bold">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</span>
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
                <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success w-100 mt-3 py-2 fw-semibold">
                    <i class="bi bi-cash-coin me-1"></i> Bayar Sisa Tagihan di Kasir
                </a>
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
@endsection
