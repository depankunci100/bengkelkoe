@extends('layouts.app')

@section('title', 'Riwayat Pembayaran Kasir')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Riwayat Pembayaran & Kasir</h4>
        <p class="text-muted small mb-0">Catatan mutasi penerimaan kasir dari transaksi servis bengkel.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('payments.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Buka Kasir Pembayaran</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<x-card>
    @if($payments->isEmpty())
        <x-empty-state title="Belum Ada Pembayaran" description="Belum ada transaksi pembayaran yang tercatat pada kasir." icon="cash-stack" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>No. Pembayaran</th>
                        <th>No. Invoice & WO</th>
                        <th>Pelanggan</th>
                        <th>Metode Bayar</th>
                        <th>Waktu Transaksi</th>
                        <th class="text-end">Nominal Diterima</th>
                        <th>Kasir</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $pay)
                        <tr>
                            <td>
                                <span class="fw-bold font-monospace text-dark">{{ $pay->payment_number }}</span>
                            </td>
                            <td>
                                <a href="{{ route('invoices.show', $pay->invoice_id) }}" class="fw-semibold text-decoration-none d-block">
                                    {{ $pay->invoice->invoice_number }}
                                </a>
                                <small class="text-muted">{{ $pay->invoice->workOrder->wo_number }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $pay->invoice->customer->name }}</span>
                            </td>
                            <td>
                                @php $badge = $pay->method_badge; @endphp
                                <span class="badge {{ $badge['class'] }} d-inline-flex align-items-center gap-1">
                                    <i class="bi {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $pay->paid_at ? $pay->paid_at->translatedFormat('d M Y, H:i') : '-' }}</td>
                            <td class="text-end fw-bold text-success fs-6">
                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge bg-light text-muted border">{{ $pay->cashier->name ?? 'Kasir' }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('invoices.print', $pay->invoice_id) }}" target="_blank" class="btn btn-sm btn-light border" title="Cetak Struk / Faktur">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</x-card>
@endsection
