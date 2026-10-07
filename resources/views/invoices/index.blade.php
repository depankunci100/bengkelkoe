@extends('layouts.app')

@section('title', 'Daftar Invoice Faktur')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Invoice & Faktur Servis</h4>
        <p class="text-muted small mb-0">Kelola tagihan pelanggan, status pelunasan, sisa saldo, dan cetak faktur resmi.</p>
    </div>
</div>
@endsection

@section('content')

<!-- Filter & Search Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('invoices.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari No. Invoice, customer, plat mobil..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="ALL">-- Semua Status Pembayaran --</option>
                    <option value="PAID" {{ $status === 'PAID' ? 'selected' : '' }}>🟢 LUNAS (Paid)</option>
                    <option value="PARTIAL" {{ $status === 'PARTIAL' ? 'selected' : '' }}>🟡 SEBAGIAN (Partial/DP)</option>
                    <option value="UNPAID" {{ $status === 'UNPAID' ? 'selected' : '' }}>🔴 BELUM LUNAS (Unpaid)</option>
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            </div>

            @if($search || $status !== 'ALL')
                <div class="col-auto">
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            @endif
        </form>
    </div>
</div>

<x-card>
    @if($invoices->isEmpty())
        <x-empty-state title="Belum Ada Invoice" description="Invoice akan otomatis dibuat saat kendaraan siap diserahterimakan." icon="receipt" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>No. Invoice</th>
                        <th>No. WO</th>
                        <th>Customer</th>
                        <th>Kendaraan & Plat</th>
                        <th>Tanggal Terbit</th>
                        <th class="text-end">Grand Total</th>
                        <th class="text-end">Dibayar</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('invoices.show', $inv->id) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('work-orders.show', $inv->work_order_id) }}" class="small text-muted text-decoration-none">
                                    {{ $inv->workOrder->wo_number }}
                                </a>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $inv->customer->name }}</span>
                            </td>
                            <td>
                                <span class="badge bg-dark fs-7">{{ $inv->workOrder->vehicle->plate_number }}</span>
                                <small class="text-muted d-block">{{ $inv->workOrder->vehicle->brand }} {{ $inv->workOrder->vehicle->model }}</small>
                            </td>
                            <td class="small text-muted">{{ $inv->issued_at ? $inv->issued_at->translatedFormat('d M Y') : $inv->created_at->format('d/m/Y') }}</td>
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($inv->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold text-success">
                                Rp {{ number_format($inv->amount_paid, 0, ',', '.') }}
                            </td>
                            <td>
                                <x-status-badge :status="$inv->payment_status" />
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-light border text-primary" title="Lihat">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="btn btn-light border text-dark" title="Cetak Faktur">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    @if($inv->payment_status !== 'PAID')
                                        <a href="{{ route('payments.create', ['invoice_id' => $inv->id]) }}" class="btn btn-success" title="Bayar Kasir">
                                            <i class="bi bi-cash-coin"></i> Bayar
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $invoices->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</x-card>

@endsection
