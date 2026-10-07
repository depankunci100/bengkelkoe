@extends('layouts.app')

@section('title', 'Faktur Retur - ' . $return->return_number)

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('supplier-returns.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">{{ $return->return_number }}</h4>
                @php $badge = $return->status_badge; @endphp
                <span class="badge {{ $badge['class'] }} fs-6 px-3 py-1">
                    <i class="bi {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                </span>
            </div>
            <small class="text-muted">Tanggal Pengajuan: {{ $return->created_at->translatedFormat('d F Y H:i') }} &bull; Dibuat Oleh: {{ $return->user->name ?? 'Gudang' }}</small>
        </div>
    </div>
    
    <div class="d-flex gap-2">
        @if($return->supplierSales && $return->supplierSales->phone)
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $return->supplierSales->phone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                }
                $waText = "Halo Bapak/Ibu " . $return->supplierSales->name . " (" . $return->supplier->name . "),\n\n"
                        . "Kami dari " . \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL') . " ingin mengonfirmasi PENGEMBALIAN BARANG RUSAK (RETUR) dengan rincian sbb:\n"
                        . "• No. Faktur Retur: " . $return->return_number . "\n"
                        . "• Sparepart: " . $return->part->name . " (" . $return->part->part_number . ")\n"
                        . "• Batch/Surat Jalan: " . ($return->batch_reference ?? '-') . "\n"
                        . "• Jumlah: " . (float)$return->quantity . " " . $return->part->unit . "\n"
                        . "• Total Nilai Retur: Rp " . number_format($return->total_amount, 0, ',', '.') . "\n"
                        . "• Alasan Kerusakan: " . $return->reason . "\n"
                        . "• Penyelesaian: " . ($return->settlement_type === 'REPLACEMENT' ? 'Tukar Barang Baru' : 'Potong Tagihan/Refund') . "\n\n"
                        . "Mohon kesediaannya untuk memproses retur barang tersebut. Terima kasih.";
            @endphp
            <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode($waText) }}" target="_blank" class="btn btn-success d-inline-flex align-items-center gap-2">
                <i class="bi bi-whatsapp"></i> Hubungi Sales via WhatsApp
            </a>
        @endif

        <a href="{{ route('supplier-returns.print', $return->id) }}" target="_blank" class="btn btn-light border text-dark d-inline-flex align-items-center gap-2">
            <i class="bi bi-printer-fill"></i> Cetak Surat Retur Resmi
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="row g-4">
    
    <!-- KOLOM KIRI: DATA SUPPLIER, SALES, & BARANG RUSAK -->
    <div class="col-12 col-lg-8">
        
        <!-- CARD SUPPLIER & SALES PENANGGUNG JAWAB -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-truck text-primary"></i> Supplier Mitra & Sales Penanggung Jawab Batch
                </h6>
                <a href="{{ route('suppliers.show', $return->supplier_id) }}" class="small text-decoration-none">
                    Lihat Profil Supplier <i class="bi bi-chevron-right"></i>
                </a>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6 border-end">
                        <span class="text-muted small text-uppercase fw-bold d-block mb-1">Perusahaan Supplier:</span>
                        <h6 class="fw-bold text-dark mb-1">{{ $return->supplier->name }}</h6>
                        <small class="text-muted d-block">Kode: <span class="font-monospace">{{ $return->supplier->code }}</span></small>
                        <small class="text-muted d-block">PIC: {{ $return->supplier->contact_person ?? '-' }}</small>
                        <small class="text-muted d-block">Telp: {{ $return->supplier->phone ?? '-' }}</small>
                        <small class="text-muted">{{ $return->supplier->address ?? '-' }}</small>
                    </div>

                    <div class="col-12 col-md-6 ps-md-4">
                        <span class="text-muted small text-uppercase fw-bold d-block mb-1">Sales Person yang Bertanggung Jawab:</span>
                        @if($return->supplierSales)
                            <div class="p-3 bg-light rounded border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold text-primary mb-0">{{ $return->supplierSales->name }}</h6>
                                    <span class="badge bg-success-subtle text-success">Sales Aktif</span>
                                </div>
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-whatsapp text-success me-1"></i> {{ $return->supplierSales->phone ?? '-' }}
                                </div>
                                @if($return->supplierSales->email)
                                    <div class="small text-muted mb-1">
                                        <i class="bi bi-envelope me-1"></i> {{ $return->supplierSales->email }}
                                    </div>
                                @endif
                                @if($return->supplierSales->area)
                                    <div class="small text-muted">
                                        <i class="bi bi-geo-alt me-1"></i> Cover Area: <strong>{{ $return->supplierSales->area }}</strong>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-secondary py-2 small mb-0">
                                <i class="bi bi-info-circle me-1"></i> Tidak ada sales spesifik yang tercatat. Hubungi kontak utama supplier.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD RINCIAN BARANG RETUR -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-box-seam text-danger"></i> Rincian Barang yang Dikembalikan
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light small text-uppercase">
                            <tr>
                                <th>Deskripsi Sparepart</th>
                                <th>Batch / Surat Jalan</th>
                                <th class="text-center" style="width: 80px;">Qty Rusak</th>
                                <th class="text-end" style="width: 140px;">Harga Beli Batch</th>
                                <th class="text-end" style="width: 150px;">Total Nilai Retur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <a href="{{ route('parts.show', $return->part_id) }}" class="fw-bold text-dark text-decoration-none d-block">
                                        {{ $return->part->name }}
                                    </a>
                                    <small class="text-muted font-monospace">Part No: {{ $return->part->part_number }} &bull; Brand: {{ $return->part->brand }}</small>
                                </td>
                                <td class="font-monospace small">
                                    {{ $return->batch_reference ?? '-' }}
                                </td>
                                <td class="text-center fw-bold text-danger">
                                    {{ (float)$return->quantity }} {{ $return->part->unit }}
                                </td>
                                <td class="text-end small">
                                    Rp {{ number_format($return->cost_price, 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    Rp {{ number_format($return->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end small text-uppercase">Total Nilai Klaim Retur:</th>
                                <th class="text-end fs-6 text-danger fw-bold">Rp {{ number_format($return->total_amount, 0, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <span class="text-muted small text-uppercase fw-bold d-block mb-1">Metode Penyelesaian:</span>
                            <span class="badge bg-primary fs-7 mb-1">{{ $return->settlement_label }}</span>
                            <small class="text-muted d-block">
                                {{ $return->settlement_type === 'REPLACEMENT' ? 'Supplier akan menukar dengan unit baru yang berfungsi normal.' : 'Nilai retur dipotongkan dari invoice hutang atau dikembalikan tunai.' }}
                            </small>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 bg-light rounded border">
                            <span class="text-muted small text-uppercase fw-bold d-block mb-1">Alasan Kerusakan / Cacat Fisik:</span>
                            <strong class="text-danger d-block">{{ $return->reason }}</strong>
                            @if($return->notes)
                                <small class="text-muted d-block mt-1">Catatan: {{ $return->notes }}</small>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- KOLOM KANAN: STATUS RETUR & TINDAK LANJUT -->
    <div class="col-12 col-lg-4 d-flex flex-column gap-4">
        
        <x-card title="Status Penanganan Retur" icon="clipboard-check">
            <div class="mb-3">
                <span class="text-muted small d-block">Status Saat Ini:</span>
                <span class="badge {{ $badge['class'] }} fs-6 px-3 py-1 mt-1">
                    <i class="bi {{ $badge['icon'] }} me-1"></i>{{ $badge['label'] }}
                </span>
            </div>

            <div class="small text-muted py-2 border-top border-bottom">
                <div class="d-flex justify-content-between mb-1">
                    <span>Tanggal Diajukan:</span>
                    <strong>{{ $return->created_at->format('d/m/Y H:i') }}</strong>
                </div>
                @if($return->resolved_at)
                    <div class="d-flex justify-content-between">
                        <span>Tanggal Selesai:</span>
                        <strong class="text-success">{{ $return->resolved_at->format('d/m/Y H:i') }}</strong>
                    </div>
                @endif
            </div>

            @if($return->status !== 'COMPLETED')
                <!-- FORM UPDATE STATUS -->
                <form action="{{ route('supplier-returns.status', $return->id) }}" method="POST" class="mt-3">
                    @csrf
                    <label class="form-label fw-semibold small">Perbarui Status Retur:</label>
                    <div class="mb-2">
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="PENDING" {{ $return->status === 'PENDING' ? 'selected' : '' }}>🟡 PENDING (Diajukan ke Sales)</option>
                            <option value="ACCEPTED" {{ $return->status === 'ACCEPTED' ? 'selected' : '' }}>🔵 ACCEPTED (Dikonfirmasi Sales)</option>
                            <option value="COMPLETED" {{ $return->status === 'COMPLETED' ? 'selected' : '' }}>🟢 COMPLETED (Barang Pengganti Diterima / Refund)</option>
                            <option value="REJECTED" {{ $return->status === 'REJECTED' ? 'selected' : '' }}>🔴 REJECTED (Ditolak Supplier)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Catatan konfirmasi sales...">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <i class="bi bi-arrow-repeat me-1"></i> Update Status Retur
                    </button>
                    <div class="form-text small text-muted mt-2" style="font-size: 0.72rem;">
                        * Jika ditandai <strong>COMPLETED</strong> dengan metode Tukar Barang, unit baru otomatis masuk kembali ke stok gudang.
                    </div>
                </form>
            @else
                <div class="text-center p-3 bg-success-subtle text-success rounded mt-3 fw-bold small">
                    <i class="bi bi-patch-check-fill fs-5 d-block mb-1"></i> KASUS RETUR SUDAH SELESAI
                </div>
            @endif
        </x-card>

        <x-card title="Bantuan Komunikasi Sales" icon="chat-dots">
            <p class="small text-muted mb-3">
                Sales representatif bertanggung jawab melayani klaim garansi produk rusak sesuai garansi distributor.
            </p>
            @if($return->supplierSales && $return->supplierSales->phone)
                <div class="d-grid gap-2">
                    <a href="tel:{{ $return->supplierSales->phone }}" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-telephone me-1"></i> Telepon Sales ({{ $return->supplierSales->phone }})
                    </a>
                </div>
            @else
                <div class="text-muted small">Nomor kontak sales tidak tersedia. Silakan hubungi nomor kantor supplier: {{ $return->supplier->phone ?? '-' }}</div>
            @endif
        </x-card>

    </div>
</div>
@endsection
