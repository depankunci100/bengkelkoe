@extends('layouts.print')

@section('title', 'Cetak Faktur ' . $invoice->invoice_number)

@section('content')

<!-- KOP BENGKEL (LOGO & IDENTITAS) -->
<div class="row align-items-center mb-4 pb-3 border-bottom border-2 border-dark">
    <div class="col-8">
        <h3 class="fw-bold mb-1 text-dark">{{ $settings['name'] }}</h3>
        <p class="small text-muted mb-0">{{ $settings['address'] }}</p>
        <p class="small text-muted mb-0">Telp/WA: {{ $settings['phone'] }} &bull; Email: {{ $settings['email'] }}</p>
    </div>
    <div class="col-4 text-end">
        <h4 class="fw-bold text-primary mb-1">FAKTUR SERVIS</h4>
        <span class="badge bg-dark fs-6 font-monospace">{{ $invoice->invoice_number }}</span>
    </div>
</div>

<!-- INFORMASI TRANSAKSI, CUSTOMER & KENDARAAN -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="p-3 bg-light rounded border">
            <span class="text-muted small text-uppercase fw-bold d-block mb-1">Informasi Pelanggan</span>
            <h6 class="fw-bold text-dark mb-1">{{ $invoice->customer->name }}</h6>
            <div class="small text-muted">No. Telepon / WA: {{ $invoice->customer->phone }}</div>
            <div class="small text-muted">Alamat: {{ $invoice->customer->address ?? '-' }}</div>
        </div>
    </div>
    <div class="col-6">
        <div class="p-3 bg-light rounded border">
            <span class="text-muted small text-uppercase fw-bold d-block mb-1">Informasi Kendaraan & WO</span>
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-muted">No. Polisi (Plat):</span>
                <span class="fw-bold text-dark">{{ $invoice->workOrder->vehicle->plate_number }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-muted">Merk / Tipe:</span>
                <span class="fw-semibold text-dark">{{ $invoice->workOrder->vehicle->brand }} {{ $invoice->workOrder->vehicle->model }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span class="small text-muted">No. Work Order:</span>
                <span class="fw-semibold text-dark">{{ $invoice->workOrder->wo_number }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="small text-muted">Tanggal Faktur:</span>
                <span class="fw-semibold text-dark">{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- TABEL DETAIL JASA -->
@php
    $services = $invoice->workOrder->items->where('type', 'SERVICE');
    $parts = $invoice->workOrder->items->where('type', 'PART');
@endphp

<h6 class="fw-bold text-dark text-uppercase small mb-2"><i class="bi bi-wrench"></i> Rincian Jasa & Ongkos Kerja</h6>
<table class="table table-sm table-bordered align-middle mb-4">
    <thead class="table-light small text-uppercase">
        <tr>
            <th style="width: 40px;" class="text-center">#</th>
            <th>Uraian Pekerjaan / Jasa</th>
            <th class="text-center" style="width: 70px;">Qty</th>
            <th class="text-end" style="width: 130px;">Tarif (Rp)</th>
            <th class="text-end" style="width: 140px;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($services as $idx => $sItem)
            <tr>
                <td class="text-center small">{{ $loop->iteration }}</td>
                <td class="fw-semibold small">{{ $sItem->item_name }}</td>
                <td class="text-center small">{{ (float) $sItem->quantity }}</td>
                <td class="text-end small">{{ number_format($sItem->unit_price, 0, ',', '.') }}</td>
                <td class="text-end small fw-bold">{{ number_format($sItem->subtotal, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted small py-2">Tidak ada item jasa.</td></tr>
        @endforelse
    </tbody>
</table>

<!-- TABEL DETAIL SPAREPART -->
<h6 class="fw-bold text-dark text-uppercase small mb-2"><i class="bi bi-box-seam"></i> Rincian Suku Cadang (Sparepart)</h6>
<table class="table table-sm table-bordered align-middle mb-4">
    <thead class="table-light small text-uppercase">
        <tr>
            <th style="width: 40px;" class="text-center">#</th>
            <th>Nama Sparepart / Part Number</th>
            <th class="text-center" style="width: 70px;">Qty</th>
            <th class="text-end" style="width: 130px;">Harga Satuan (Rp)</th>
            <th class="text-end" style="width: 140px;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($parts as $pItem)
            <tr>
                <td class="text-center small">{{ $loop->iteration }}</td>
                <td class="fw-semibold small">{{ $pItem->item_name }}</td>
                <td class="text-center small">{{ (float) $pItem->quantity }}</td>
                <td class="text-end small">{{ number_format($pItem->unit_price, 0, ',', '.') }}</td>
                <td class="text-end small fw-bold">{{ number_format($pItem->subtotal, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted small py-2">Tidak ada item suku cadang.</td></tr>
        @endforelse
    </tbody>
</table>

<!-- RINGKASAN TOTAL PEMBAYARAN -->
<div class="row justify-content-end mb-4">
    <div class="col-6">
        <table class="table table-sm table-borderless mb-0">
            <tr>
                <td class="text-muted small">Subtotal:</td>
                <td class="text-end fw-semibold">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->discount > 0)
                <tr>
                    <td class="text-success small">Potongan Diskon:</td>
                    <td class="text-end text-success fw-semibold">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-muted small">Pajak PPN (11%):</td>
                <td class="text-end fw-semibold">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
            </tr>
            <tr class="border-top border-dark">
                <td class="fw-bold fs-6">Grand Total:</td>
                <td class="text-end fw-bold fs-6 text-primary">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-muted small">Telah Dibayar (Payment):</td>
                <td class="text-end fw-bold text-success">Rp {{ number_format($invoice->amount_paid, 0, ',', '.') }}</td>
            </tr>
            <tr class="border-top">
                <td class="fw-bold small">Sisa Tagihan (Balance Due):</td>
                <td class="text-end fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-muted' }}">
                    Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>
</div>

<!-- TANDA TANGAN & FOOTER -->
<div class="row pt-4 text-center">
    <div class="col-4">
        <small class="text-muted d-block mb-4">Pelanggan,</small>
        <div style="height: 40px;"></div>
        <div class="fw-bold text-dark border-top pt-1 small">({{ $invoice->customer->name }})</div>
    </div>
    <div class="col-4">
        <small class="text-muted d-block mb-4">Mekanik Penanggung Jawab,</small>
        <div style="height: 40px;"></div>
        <div class="fw-bold text-dark border-top pt-1 small">({{ $invoice->workOrder->technician->name ?? 'Mekanik' }})</div>
    </div>
    <div class="col-4">
        <small class="text-muted d-block mb-4">Kasir Bengkel,</small>
        <div style="height: 40px;"></div>
        <div class="fw-bold text-dark border-top pt-1 small">({{ $invoice->cashier->name ?? auth()->user()->name ?? 'Kasir' }})</div>
    </div>
</div>

<div class="text-center text-muted small mt-4 pt-3 border-top">
    <em>{{ $settings['footer'] }}</em>
</div>

@endsection
