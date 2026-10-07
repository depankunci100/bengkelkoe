@extends('layouts.print')

@section('title', 'Cetak Faktur Retur ' . $return->return_number)

@section('content')

<!-- KOP BENGKEL -->
<div class="row align-items-center mb-4 pb-3 border-bottom border-2 border-dark">
    <div class="col-8">
        <h3 class="fw-bold mb-1 text-dark">{{ $settings['name'] }}</h3>
        <p class="small text-muted mb-0">{{ $settings['address'] }}</p>
        <p class="small text-muted mb-0">Telp/WA: {{ $settings['phone'] }} &bull; Email: {{ $settings['email'] }}</p>
    </div>
    <div class="col-4 text-end">
        <h5 class="fw-bold text-danger mb-1">NOTA RETUR BARANG</h5>
        <span class="badge bg-dark fs-6 font-monospace">{{ $return->return_number }}</span>
    </div>
</div>

<!-- INFORMASI SUPPLIER & SALES TUJUAN -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="p-3 bg-light rounded border">
            <span class="text-muted small text-uppercase fw-bold d-block mb-1">Kepada Supplier Mitra:</span>
            <h6 class="fw-bold text-dark mb-1">{{ $return->supplier->name }}</h6>
            <div class="small text-muted">Kode: {{ $return->supplier->code }}</div>
            <div class="small text-muted">PIC: {{ $return->supplier->contact_person ?? '-' }} &bull; Telp: {{ $return->supplier->phone ?? '-' }}</div>
            <div class="small text-muted">Alamat: {{ $return->supplier->address ?? '-' }}</div>
        </div>
    </div>
    <div class="col-6">
        <div class="p-3 bg-light rounded border">
            <span class="text-muted small text-uppercase fw-bold d-block mb-1">Sales Person Penanggung Jawab:</span>
            @if($return->supplierSales)
                <h6 class="fw-bold text-primary mb-1">{{ $return->supplierSales->name }}</h6>
                <div class="small text-muted">No. Telepon / WhatsApp: {{ $return->supplierSales->phone ?? '-' }}</div>
                <div class="small text-muted">Wilayah Cover: {{ $return->supplierSales->area ?? '-' }}</div>
            @else
                <div class="small text-muted">Kontak Umum Perusahaan Supplier</div>
            @endif
            <div class="small text-muted mt-2 border-top pt-1">
                Tanggal Pengajuan: <strong>{{ $return->created_at->format('d/m/Y H:i') }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- TABEL RINCIAN BARANG RUSAK -->
<h6 class="fw-bold text-dark text-uppercase small mb-2"><i class="bi bi-box-seam"></i> Rincian Barang / Suku Cadang yang Dikembalikan</h6>
<table class="table table-sm table-bordered align-middle mb-4">
    <thead class="table-light small text-uppercase">
        <tr>
            <th style="width: 40px;" class="text-center">#</th>
            <th>Part Number & Deskripsi Sparepart</th>
            <th class="text-center" style="width: 140px;">No. Batch / SJ Asal</th>
            <th class="text-center" style="width: 80px;">Qty Rusak</th>
            <th class="text-end" style="width: 140px;">Harga Satuan (Rp)</th>
            <th class="text-end" style="width: 150px;">Total Nilai (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center small">1</td>
            <td>
                <span class="fw-bold text-dark">{{ $return->part->name }}</span>
                <div class="small text-muted">Part No: {{ $return->part->part_number }} &bull; Brand: {{ $return->part->brand }}</div>
            </td>
            <td class="text-center font-monospace small">
                {{ $return->batch_reference ?? '-' }}
            </td>
            <td class="text-center fw-bold text-danger">
                {{ (float)$return->quantity }} {{ $return->part->unit }}
            </td>
            <td class="text-end small">
                {{ number_format($return->cost_price, 0, ',', '.') }}
            </td>
            <td class="text-end fw-bold text-dark">
                {{ number_format($return->total_amount, 0, ',', '.') }}
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="table-light">
            <th colspan="5" class="text-end small text-uppercase">Total Nilai Pengembalian Barang:</th>
            <th class="text-end fs-6 fw-bold text-danger">Rp {{ number_format($return->total_amount, 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>

<!-- ALASAN KERUSAKAN & METODE PENYELESAIAN -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="p-3 bg-light rounded border">
            <span class="small text-muted fw-bold text-uppercase d-block mb-1">Alasan Kerusakan / Kondisi Fisik:</span>
            <div class="fw-semibold text-danger">{{ $return->reason }}</div>
            @if($return->notes)
                <div class="small text-muted mt-1">{{ $return->notes }}</div>
            @endif
        </div>
    </div>
    <div class="col-6">
        <div class="p-3 bg-light rounded border">
            <span class="small text-muted fw-bold text-uppercase d-block mb-1">Metode Penyelesaian yang Diminta:</span>
            <div class="fw-semibold text-dark">{{ $return->settlement_label }}</div>
            <small class="text-muted d-block mt-1">
                {{ $return->settlement_type === 'REPLACEMENT' ? 'Mohon segera dikirimkan unit pengganti baru yang berfungsi normal.' : 'Mohon dikurangkan pada tagihan faktur pembelian berikutnya.' }}
            </small>
        </div>
    </div>
</div>

<!-- TANDA TANGAN & SERAH TERIMA -->
<div class="row pt-4 text-center">
    <div class="col-4">
        <small class="text-muted d-block mb-4">Dibuat Oleh (Admin Gudang),</small>
        <div style="height: 45px;"></div>
        <div class="fw-bold text-dark border-top pt-1 small">({{ $return->user->name ?? 'Admin Gudang' }})</div>
    </div>
    <div class="col-4">
        <small class="text-muted d-block mb-4">Disetujui Oleh (Kepala Bengkel),</small>
        <div style="height: 45px;"></div>
        <div class="fw-bold text-dark border-top pt-1 small">({{ auth()->user()->name ?? 'Kepala Bengkel' }})</div>
    </div>
    <div class="col-4">
        <small class="text-muted d-block mb-4">Diterima Oleh (Sales / Supplier),</small>
        <div style="height: 45px;"></div>
        <div class="fw-bold text-dark border-top pt-1 small">({{ $return->supplierSales->name ?? 'Sales / Kurir' }})</div>
    </div>
</div>

<div class="text-center text-muted small mt-5 pt-3 border-top">
    <em>{{ $settings['footer'] }} &bull; Dicetak otomatis oleh SIM BENGKEL pada {{ date('d/m/Y H:i') }}</em>
</div>

@endsection
