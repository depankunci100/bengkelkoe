@extends('layouts.app')

@section('title', 'Work Order ' . $wo->wo_number)

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('work-orders.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">{{ $wo->wo_number }}</h4>
                <x-status-badge :status="$wo->status" class="fs-6 px-3 py-1" />
            </div>
            <small class="text-muted">Dibuat pada {{ $wo->created_at->translatedFormat('d F Y, H:i') }} &bull; Didaftarkan oleh: {{ $wo->creator->name ?? 'Front Office' }}</small>
        </div>
    </div>

    <!-- Quick Action Buttons -->
    <div class="d-flex flex-wrap gap-2">
        <!-- Tombol WhatsApp Approval -->
        <button type="button" class="btn btn-success d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#whatsappModal">
            <i class="bi bi-whatsapp"></i>
            <span>Kirim WhatsApp</span>
        </button>

        <!-- Tombol Tambah Item -->
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="bi bi-plus-lg"></i>
            <span>Tambah Item / Jasa</span>
        </button>

        <!-- Tombol Pekerjaan Tambahan -->
        <button type="button" class="btn btn-outline-warning text-dark d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#additionalWorkModal">
            <i class="bi bi-tools"></i>
            <span>Pekerjaan Tambahan</span>
        </button>

        <!-- Tombol Update Status Alur -->
        <div class="dropdown">
            <button class="btn btn-dark dropdown-toggle d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-arrow-repeat"></i> Ganti Status
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><h6 class="dropdown-header">Tahapan Alur Pengerjaan</h6></li>
                @foreach(['INSPECTION' => 'Mulai Inspeksi', 'WAITING_APPROVAL' => 'Kirim Menunggu Approval', 'APPROVED' => 'Tandai Disetujui', 'IN_PROGRESS' => 'Mulai Pengerjaan', 'QC' => 'Masuk Tahap QC', 'READY_FOR_PICKUP' => 'Siap Diambil', 'COMPLETED' => 'Selesai & Serah Terima'] as $stKey => $stLabel)
                    @if($stKey !== $wo->status)
                        <li>
                            <form action="{{ route('work-orders.update-status', $wo->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="{{ $stKey }}">
                                <button type="submit" class="dropdown-item py-2">{{ $stLabel }}</button>
                            </form>
                        </li>
                    @endif
                @endforeach
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button class="dropdown-item text-danger py-2" data-bs-toggle="modal" data-bs-target="#cancelWoModal">
                        <i class="bi bi-slash-circle me-1"></i> Batalkan Work Order
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('content')

<!-- STATUS ALERT / INFO BANNER -->
@if($wo->status === 'WAITING_APPROVAL')
    <div class="alert alert-warning border-warning shadow-sm d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history fs-3 text-warning"></i>
            <div>
                <strong>Menunggu Persetujuan Pelanggan!</strong>
                <div class="small">Tautan approval mandiri telah dibuat. Pelanggan dapat meninjau rincian biaya melalui web mandiri atau via WhatsApp.</div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('/approval/' . $wo->approval_token) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Link Customer
            </a>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#whatsappModal">
                <i class="bi bi-whatsapp me-1"></i> Kirim Ulang WA
            </button>
        </div>
    </div>
@elseif($wo->status === 'READY_FOR_PICKUP')
    <div class="alert alert-success border-success shadow-sm d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-3 text-success"></i>
            <div>
                <strong>Kendaraan Siap Diambil!</strong>
                <div class="small">Seluruh servis telah selesai dan lolos inspeksi QC. Kasir dapat memproses pembayaran dan pencetakan faktur.</div>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if($wo->invoice)
                <a href="{{ route('payments.create', ['invoice_id' => $wo->invoice->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-cash-coin me-1"></i> Proses Pembayaran di Kasir
                </a>
            @else
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createWoInvoiceModal">
                    <i class="bi bi-receipt me-1"></i> Terbitkan Invoice & Diskon
                </button>
            @endif
        </div>
    </div>
@endif

<div class="row g-4">

    <!-- SISI KIRI: INFORMASI UTAMA & RINCIAN ITEM -->
    <div class="col-12 col-lg-8">

        <!-- 1. KARTU INFORMASI UTAMA (Section 11) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Customer -->
                    <div class="col-12 col-md-6 border-end-md">
                        <span class="text-muted small text-uppercase fw-bold d-block mb-1">
                            <i class="bi bi-person text-primary"></i> Customer
                        </span>
                        <h6 class="fw-bold text-dark mb-1">
                            <a href="{{ route('customers.show', $wo->customer_id) }}" class="text-dark text-decoration-none">
                                {{ $wo->customer->name }}
                            </a>
                        </h6>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-whatsapp text-success"></i> {{ $wo->customer->phone }}
                        </div>
                        <div class="small text-muted">
                            {{ $wo->customer->address ?? 'Alamat tidak terisi' }}
                        </div>
                    </div>

                    <!-- Vehicle -->
                    <div class="col-12 col-md-6">
                        <span class="text-muted small text-uppercase fw-bold d-block mb-1">
                            <i class="bi bi-car-front text-primary"></i> Kendaraan
                        </span>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-dark fs-6 px-2 py-1">{{ $wo->vehicle->plate_number }}</span>
                            <span class="fw-bold text-dark">{{ $wo->vehicle->brand }} {{ $wo->vehicle->model }}</span>
                        </div>
                        <div class="small text-muted mb-1">
                            Transmisi: {{ $wo->vehicle->transmission }} &bull; Warna: {{ $wo->vehicle->color ?? '-' }} ({{ $wo->vehicle->year ?? '-' }})
                        </div>
                        <div class="small text-dark fw-semibold">
                            <i class="bi bi-speedometer2 text-muted"></i> Odometer Masuk: {{ number_format($wo->odometer_in ?? $wo->vehicle->odometer, 0, ',', '.') }} KM
                        </div>
                    </div>
                </div>

                <hr class="my-3 text-muted">

                <!-- Keluhan Pelanggan -->
                <div class="mb-3">
                    <span class="text-muted small text-uppercase fw-bold d-block mb-1">
                        <i class="bi bi-chat-left-dots text-danger"></i> Keluhan Pelanggan
                    </span>
                    <div class="p-3 bg-light rounded text-dark border">
                        {{ $wo->complaint }}
                    </div>
                </div>

                <!-- Teknisi Penanggung Jawab -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 p-3 bg-light rounded border">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold d-block">Mekanik / Teknisi Penanggung Jawab</span>
                        <div class="fw-bold text-dark">
                            @if($wo->technician)
                                <i class="bi bi-person-badge-fill text-primary"></i> {{ $wo->technician->name }}
                                <span class="badge bg-primary-subtle text-primary border ms-1">{{ $wo->technician->specialization }}</span>
                            @else
                                <span class="text-danger"><i class="bi bi-exclamation-circle"></i> Belum ada teknisi ditugaskan</span>
                            @endif
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changeTechModal">
                        <i class="bi bi-person-gear"></i> Ganti / Tugaskan Teknisi
                    </button>
                </div>

            </div>
        </div>

        <!-- 2. HASIL INSPEKSI KENDARAAN (Section 14) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-clipboard2-check text-primary"></i> Hasil Pemeriksaan & Diagnosa (Inspeksi)
                </h6>
                <a href="{{ route('inspections.show', $wo->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil-square me-1"></i> Buka Lembar Inspeksi Lengkap
                </a>
            </div>
            <div class="card-body">
                @if(!$wo->inspection || $wo->inspection->items->isEmpty())
                    <p class="text-muted small mb-0">Belum ada catatan inspeksi fisik kendaraan.</p>
                @else
                    <div class="row g-2">
                        @foreach($wo->inspection->items as $insItem)
                            <div class="col-12 col-md-6">
                                <div class="p-2 border rounded d-flex justify-content-between align-items-center bg-light">
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $insItem->category }}</small>
                                        <span class="fw-semibold small text-dark">{{ $insItem->item_name }}</span>
                                        @if($insItem->notes)
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $insItem->notes }}</div>
                                        @endif
                                    </div>
                                    <x-status-badge :status="$insItem->condition" style="font-size: 0.72rem;" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 3. ESTIMASI BIAYA & PEKERJAAN (JASA & SPAREPART) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-cash-stack text-primary"></i> Estimasi Rincian Pekerjaan & Suku Cadang
                    </h6>
                    @if($wo->items->where('type', 'PART')->count() > 0)
                        @php $prog = $wo->partVerificationProgress(); @endphp
                        <small class="text-muted d-block mt-1">
                            Verifikasi Gudang: 
                            <span class="badge {{ $prog['is_complete'] ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $prog['verified'] }}/{{ $prog['total'] }} Part Terverifikasi ({{ $prog['percent'] }}%)
                            </span>
                        </small>
                    @endif
                </div>
                <div class="d-flex flex-wrap gap-2">
                    @if($wo->items->where('type', 'PART')->count() > 0)
                        <a href="{{ route('scanner.index', ['mode' => 'dispatch', 'wo_id' => $wo->id]) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-upc-scan"></i> Scan Verifikasi Gudang
                        </a>
                        <a href="{{ route('work-orders.picking-slip', $wo->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-printer"></i> Picking Slip
                        </a>
                    @endif
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Item
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($wo->items->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-cart-x fs-1 opacity-50 d-block mb-2"></i>
                        <p class="mb-0">Belum ada item jasa atau sparepart yang dimasukkan ke estimasi ini.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small text-uppercase">
                                <tr>
                                    <th>Tipe</th>
                                    <th>Item Deskripsi</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-center">Status Item</th>
                                    <th class="text-end" style="width: 50px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($wo->items as $item)
                                    <tr>
                                        <td>
                                            @if($item->type === 'SERVICE')
                                                <span class="badge bg-info-subtle text-info border">JASA</span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary border">PART</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark d-block">{{ $item->item_name }}</span>
                                            @if($item->is_additional)
                                                <span class="badge bg-warning text-dark" style="font-size: 0.68rem;">
                                                    <i class="bi bi-exclamation-triangle"></i> Pekerjaan Tambahan
                                                </span>
                                            @endif
                                            @if($item->notes)
                                                <small class="text-muted d-block">{{ $item->notes }}</small>
                                            @endif
                                            @if($item->type === 'PART')
                                                @if($item->isFullyVerified())
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.7rem;">
                                                        <i class="bi bi-shield-check me-1"></i>Verifikasi Gudang ({{ (float)$item->verified_quantity }}/{{ (float)$item->quantity }})
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle mt-1" style="font-size: 0.7rem;">
                                                        <i class="bi bi-clock me-1"></i>Belum Diverifikasi Gudang ({{ (float)$item->verified_quantity }}/{{ (float)$item->quantity }})
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-center fw-semibold">
                                            {{ (float) $item->quantity }}
                                        </td>
                                        <td class="text-end small">
                                            Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            @if($item->approval_status === 'APPROVED')
                                                <span class="badge bg-success">APPROVED</span>
                                            @elseif($item->approval_status === 'REJECTED')
                                                <span class="badge bg-danger">REJECTED</span>
                                            @else
                                                <span class="badge bg-warning text-dark">PENDING</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('work-orders.remove-item', [$wo->id, $item->id]) }}" method="POST" onsubmit="return confirm('Hapus item ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger p-1" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Ringkasan Total Biaya -->
            <div class="card-footer bg-light p-3">
                <div class="row justify-content-end">
                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Total Jasa:</span>
                            <span class="fw-semibold">Rp {{ number_format($wo->total_services, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Total Sparepart:</span>
                            <span class="fw-semibold">Rp {{ number_format($wo->total_parts, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-semibold">Rp {{ number_format($wo->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($wo->discount > 0)
                            <div class="d-flex justify-content-between py-1 small text-success">
                                <span>
                                    Diskon:
                                    @if($wo->discount_type === 'PERCENT' && $wo->discount_percent > 0)
                                        ({{ (float)$wo->discount_percent }}%)
                                    @endif
                                </span>
                                <span class="fw-semibold">- Rp {{ number_format($wo->discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">PPN (11%):</span>
                            <span class="fw-semibold">Rp {{ number_format($wo->tax, 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between py-1">
                            <span class="fw-bold fs-6 text-dark">Grand Total:</span>
                            <span class="fw-bold fs-6 text-primary">Rp {{ number_format($wo->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- SISI KANAN: AUDIT TIMELINE & LOG WHATSAPP (Section 12 & 17) -->
    <div class="col-12 col-lg-4 d-flex flex-column gap-4">

        <!-- KARTU ACTIONS RINGKAS -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-lightning-charge text-warning"></i> Aksi Cepat
                </h6>
            </div>
            <div class="card-body d-grid gap-2">
                <button type="button" class="btn btn-success d-flex align-items-center justify-content-between py-2" data-bs-toggle="modal" data-bs-target="#whatsappModal">
                    <span><i class="bi bi-whatsapp me-2"></i> Kirim WhatsApp Approval</span>
                    <i class="bi bi-chevron-right small"></i>
                </button>

                <a href="{{ url('/approval/' . $wo->approval_token) }}" target="_blank" class="btn btn-outline-dark d-flex align-items-center justify-content-between py-2">
                    <span><i class="bi bi-link-45deg me-2"></i> Buka Portal Approval Pelanggan</span>
                    <i class="bi bi-box-arrow-up-right small"></i>
                </a>

                @if($wo->invoice)
                    <a href="{{ route('invoices.show', $wo->invoice->id) }}" class="btn btn-outline-primary d-flex align-items-center justify-content-between py-2">
                        <span><i class="bi bi-receipt me-2"></i> Buka Faktur Invoice</span>
                        <i class="bi bi-chevron-right small"></i>
                    </a>
                @elseif(in_array($wo->status, ['APPROVED', 'IN_PROGRESS', 'QC', 'READY_FOR_PICKUP', 'COMPLETED']))
                    <button type="button" class="btn btn-primary d-flex align-items-center justify-content-between py-2" data-bs-toggle="modal" data-bs-target="#createWoInvoiceModal">
                        <span><i class="bi bi-receipt-cutoff me-2"></i> Terbitkan Invoice & Diskon</span>
                        <i class="bi bi-chevron-right small"></i>
                    </button>
                @endif
            </div>
        </div>

        <!-- TIMELINE PENGERJAAN REAL (Section 12) -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i> Timeline Pengerjaan & Audit Log
                </h6>
            </div>
            <div class="card-body p-3">
                @if($wo->timelines->isEmpty())
                    <p class="text-muted small text-center my-3">Belum ada riwayat timeline tercatat.</p>
                @else
                    <div class="position-relative ps-3 border-start border-2 border-primary ms-2">
                        @foreach($wo->timelines as $tl)
                            <div class="position-relative mb-4">
                                <span class="position-absolute translate-middle bg-primary rounded-circle" style="left: -13px; top: 8px; width: 12px; height: 12px;"></span>
                                <div>
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <h6 class="fw-bold text-dark mb-0 small">{{ $tl->title }}</h6>
                                        <span class="text-muted" style="font-size: 0.72rem;">{{ $tl->created_at->format('H:i, d/m') }}</span>
                                    </div>
                                    <p class="text-muted small mb-1 mt-1">{{ $tl->description }}</p>
                                    @if($tl->user)
                                        <small class="badge bg-light text-muted border" style="font-size: 0.68rem;">
                                            <i class="bi bi-person"></i> {{ $tl->user->name }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- RIWAYAT LOG WHATSAPP (Section 17) -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="card-title mb-0 fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-chat-left-text text-success"></i> Riwayat Pesan WhatsApp
                </h6>
            </div>
            <div class="card-body p-3">
                @if($wo->whatsappLogs->isEmpty())
                    <p class="text-muted small text-center my-3">Belum pernah mengirim pesan WhatsApp untuk WO ini.</p>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($wo->whatsappLogs as $wLog)
                            <div class="p-2 bg-light border rounded">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-success-subtle text-success border small">{{ $wLog->status }}</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $wLog->created_at->translatedFormat('d M, H:i') }}</small>
                                </div>
                                <div class="small text-dark text-truncate" title="{{ $wLog->message }}">
                                    {{ $wLog->message }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

</div>

<!-- ========================================== -->
<!-- MODAL-MODAL AKSI (Bootstrap 5 Modals)      -->
<!-- ========================================== -->

<!-- 1. MODAL PREVIEW & KIRIM WHATSAPP (Section 17) -->
<x-modal id="whatsappModal" title="Kirim Approval ke WhatsApp Pelanggan" size="lg">
    @php
        $approvalUrl = url('/approval/' . ($wo->approval_token ?? 'temp'));
        $defaultMsg = "Halo Bapak/Ibu {$wo->customer->name},\n\nEstimasi perbaikan untuk kendaraan {$wo->vehicle->brand} {$wo->vehicle->model} (No. Plat: {$wo->vehicle->plate_number}) dengan No. WO: {$wo->wo_number} telah selesai kami siapkan.\n\nTotal Estimasi: Rp " . number_format($wo->grand_total, 0, ',', '.') . "\n\nSilakan tinjau rincian pekerjaan dan lakukan persetujuan (approval) secara online melalui tautan resmi kami:\n{$approvalUrl}\n\nTerima kasih,\n" . \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL');
    @endphp

    <form action="{{ route('work-orders.send-whatsapp', $wo->id) }}" method="POST">
        @csrf
        
        <div class="bg-light p-3 rounded border mb-3">
            <div class="row g-2 small">
                <div class="col-6">
                    <span class="text-muted">Nama Pelanggan:</span>
                    <strong class="d-block text-dark">{{ $wo->customer->name }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">Nomor WhatsApp:</span>
                    <strong class="d-block text-success">{{ $wo->customer->phone }}</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">Kendaraan:</span>
                    <strong class="d-block text-dark">{{ $wo->vehicle->plate_number }} ({{ $wo->vehicle->model }})</strong>
                </div>
                <div class="col-6">
                    <span class="text-muted">Grand Total Estimasi:</span>
                    <strong class="d-block text-primary">Rp {{ number_format($wo->grand_total, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label fw-semibold">Isi Pesan WhatsApp</label>
            <textarea name="message" id="message" rows="8" class="form-control font-monospace" style="font-size: 0.85rem;" required>{{ $defaultMsg }}</textarea>
            <div class="form-text text-muted">Pesan ini akan disimulasikan / dikirimkan melalui WhatsApp API gateway ke nomor pelanggan.</div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                <i class="bi bi-whatsapp"></i> Kirim WhatsApp Sekarang
            </button>
        </div>
    </form>
</x-modal>

<!-- 2. MODAL TAMBAH ITEM REGULER -->
<x-modal id="addItemModal" title="Tambah Item Jasa atau Sparepart">
    <form action="{{ route('work-orders.add-item', $wo->id) }}" method="POST" x-data="{ itemType: 'SERVICE' }">
        @csrf
        
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Tipe Item</label>
            <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="type" id="typeService" value="SERVICE" x-model="itemType">
                <label class="btn btn-outline-primary" for="typeService"><i class="bi bi-wrench"></i> Jasa / Layanan</label>

                <input type="radio" class="btn-check" name="type" id="typePart" value="PART" x-model="itemType">
                <label class="btn btn-outline-primary" for="typePart"><i class="bi bi-box-seam"></i> Suku Cadang (Part)</label>
            </div>
        </div>

        <!-- Dropdown Jasa -->
        <div class="mb-3" x-show="itemType === 'SERVICE'">
            <label class="form-label fw-semibold">Pilih Jasa</label>
            <select name="service_id" class="form-select">
                <option value="">-- Pilih Jasa Servis --</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} (Rp {{ number_format($s->price, 0, ',', '.') }})</option>
                @endforeach
            </select>
        </div>

        <!-- Dropdown Sparepart -->
        <div class="mb-3" x-show="itemType === 'PART'">
            <label class="form-label fw-semibold">Pilih Sparepart</label>
            <select name="part_id" class="form-select">
                <option value="">-- Pilih Sparepart --</option>
                @foreach($parts as $p)
                    <option value="{{ $p->id }}" {{ $p->stock <= 0 ? 'disabled' : '' }}>
                        {{ $p->name }} - Stok: {{ $p->stock }} (Rp {{ number_format($p->selling_price, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label fw-semibold">Jumlah (Qty)</label>
            <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1" step="1" required>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Tambahkan ke Estimasi</button>
        </div>
    </form>
</x-modal>

<!-- 3. MODAL PEKERJAAN TAMBAHAN (Section 19) -->
<x-modal id="additionalWorkModal" title="Pengajuan Pekerjaan / Part Tambahan">
    <div class="alert alert-warning small mb-3">
        Gunakan formulir ini jika saat pembongkaran ditemukan kerusakan tak terduga yang membutuhkan persetujuan tambahan dari pelanggan.
    </div>

    <form action="{{ route('work-orders.add-additional', $wo->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-semibold">Tipe Pekerjaan</label>
            <select name="type" class="form-select" required>
                <option value="SERVICE">Jasa Tambahan</option>
                <option value="PART">Sparepart Tambahan</option>
            </select>
        </div>

        <x-form.input name="item_name" label="Nama Pekerjaan / Sparepart" placeholder="Contoh: Service AC / Evaporator Bocor" required />
        
        <div class="row">
            <div class="col-6">
                <x-form.input name="quantity" type="number" label="Jumlah" value="1" min="1" required />
            </div>
            <div class="col-6">
                <x-form.input name="unit_price" type="number" label="Biaya (Rp)" placeholder="500000" required />
            </div>
        </div>

        <x-form.textarea name="reason" label="Alasan / Temuan Kerusakan" placeholder="Misal: Evaporator ditemukan keropos dan berlumut tebal saat dites tekanan..." rows="3" required help="Alasan ini akan ditampilkan kepada customer saat meminta persetujuan." />

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-warning text-dark fw-bold">Ajukan Pekerjaan Tambahan</button>
        </div>
    </form>
</x-modal>

<!-- 4. MODAL GANTI TEKNISI -->
<x-modal id="changeTechModal" title="Tugaskan Teknisi (Mekanik)">
    <form action="{{ route('work-orders.assign-tech', $wo->id) }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="tech_id" class="form-label fw-semibold">Pilih Teknisi</label>
            <select name="technician_id" id="tech_id" class="form-select" required>
                @foreach($technicians as $tech)
                    <option value="{{ $tech->id }}" {{ $wo->technician_id == $tech->id ? 'selected' : '' }}>
                        {{ $tech->name }} ({{ $tech->specialization ?? 'Mekanik' }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Penugasan</button>
        </div>
    </form>
</x-modal>

<!-- 5. MODAL BATALKAN WORK ORDER (Section 27: Confirmation Modal) -->
<x-modal id="cancelWoModal" title="Konfirmasi Batalkan Work Order">
    <p class="text-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill fs-4 me-2 align-middle"></i>
        Apakah Anda yakin ingin membatalkan Work Order <strong>{{ $wo->wo_number }}</strong>?
    </p>
    <form action="{{ route('work-orders.update-status', $wo->id) }}" method="POST">
        @csrf
        <input type="hidden" name="status" value="CANCELLED">
        <div class="mb-3">
            <label class="form-label small fw-semibold">Alasan Pembatalan</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Tulis alasan pembatalan..." required></textarea>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tidak</button>
            <button type="submit" class="btn btn-danger">Ya, Batalkan Work Order</button>
        </div>
    </form>
</x-modal>

<!-- MODAL TERBITKAN INVOICE DENGAN DISKON -->
@if(!$wo->invoice)
<div class="modal fade" id="createWoInvoiceModal" tabindex="-1" aria-labelledby="createWoInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('invoices.store') }}" method="POST" x-data="woInvoiceDiscountModal({
                subtotal: {{ (float)$wo->subtotal }},
                initialType: '{{ $wo->discount_type ?? 'FIXED' }}',
                initialValue: {{ $wo->discount_type === 'PERCENT' ? (float)($wo->discount_percent ?? 0) : (float)($wo->discount ?? 0) }},
                initialReason: '{{ addslashes($wo->discount_reason ?? '') }}',
                includeTax: true
            })">
                @csrf
                <input type="hidden" name="work_order_id" value="{{ $wo->id }}">

                <div class="modal-header bg-primary text-white py-3">
                    <h6 class="modal-title fw-bold" id="createWoInvoiceModalLabel">
                        <i class="bi bi-receipt-cutoff me-1"></i> Terbitkan Invoice & Atur Diskon
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">No. Work Order:</span>
                            <span class="fw-bold font-monospace">{{ $wo->wo_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Pelanggan:</span>
                            <span class="fw-semibold text-dark">{{ $wo->customer->name }} ({{ $wo->vehicle->plate_number }})</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-1 mt-1">
                            <span class="small text-muted">Subtotal Jasa & Part:</span>
                            <strong class="text-primary fs-6" x-text="formatRupiah(subtotal)"></strong>
                        </div>
                    </div>

                    <!-- Jenis Diskon -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Metode / Jenis Diskon</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="discount_type" id="woModalDiscFixed" value="FIXED" x-model="discountType" @change="recalculate()">
                            <label class="btn btn-outline-success py-2 btn-sm fw-semibold" for="woModalDiscFixed">Nominal (Rp)</label>

                            <input type="radio" class="btn-check" name="discount_type" id="woModalDiscPercent" value="PERCENT" x-model="discountType" @change="recalculate()">
                            <label class="btn btn-outline-success py-2 btn-sm fw-semibold" for="woModalDiscPercent">Persen (%)</label>
                        </div>
                    </div>

                    <!-- Input Nilai Diskon -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">
                            <span x-show="discountType === 'FIXED'">Nominal Potongan Diskon (Rp)</span>
                            <span x-show="discountType === 'PERCENT'">Persentase Potongan Diskon (%)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light" x-text="discountType === 'FIXED' ? 'Rp' : '%'"></span>
                            <input type="number" 
                                   step="any" 
                                   min="0" 
                                   name="discount_value" 
                                   class="form-control fw-bold text-success fs-5" 
                                   placeholder="0" 
                                   x-model.number="discountValue" 
                                   @input="recalculate()">
                        </div>
                        <small class="text-muted" x-show="discountType === 'PERCENT'">
                            Nilai potongan: <strong class="text-success" x-text="formatRupiah(calculatedDiscountAmount)"></strong>
                        </small>
                    </div>

                    <!-- Preset Cepat -->
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Preset Diskon Cepat:</label>
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
                        <label class="form-label fw-semibold small">Alasan / Kategori Diskon (Opsional)</label>
                        <input type="text" 
                               name="discount_reason" 
                               class="form-control form-control-sm" 
                               placeholder="Contoh: Promo Member, Diskon Rekanan..." 
                               x-model="discountReason">
                    </div>

                    <!-- PPN Switch -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" name="include_tax" id="woModalIncludeTax" value="1" x-model="includeTax" @change="recalculate()">
                        <label class="form-check-label small fw-semibold" for="woModalIncludeTax">Kenakan PPN (11%)</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan Faktur (Opsional)</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Catatan tambahan di faktur...">
                    </div>

                    <!-- Preview Kalkulasi Realtime -->
                    <div class="p-3 bg-light rounded border">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Potongan Diskon:</span>
                            <strong class="text-success" x-text="'- ' + formatRupiah(calculatedDiscountAmount)"></strong>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Dasar Pengenaan Pajak (DPP):</span>
                            <span class="fw-semibold text-dark" x-text="formatRupiah(taxableBase)"></span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Pajak PPN (11%):</span>
                            <span class="fw-semibold text-dark" x-text="formatRupiah(taxAmount)"></span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                            <span class="fw-bold text-dark fs-6">Grand Total Tagihan:</span>
                            <strong class="text-primary fs-5" x-text="formatRupiah(grandTotal)"></strong>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                    <a href="{{ route('invoices.create', ['work_order_id' => $wo->id]) }}" class="small text-decoration-none">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Lengkap
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Terbitkan Invoice
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function woInvoiceDiscountModal(config) {
    return {
        subtotal: config.subtotal || 0,
        discountType: config.initialType || 'FIXED',
        discountValue: config.initialValue || 0,
        discountReason: config.initialReason || '',
        includeTax: config.includeTax !== false,

        calculatedDiscountAmount: 0,
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
                this.calculatedDiscountAmount = Math.round((sub * val) / 100);
            } else {
                if (val > sub) val = sub;
                this.calculatedDiscountAmount = val;
            }

            this.taxableBase = Math.max(0, sub - this.calculatedDiscountAmount);
            this.taxAmount = this.includeTax ? Math.round(this.taxableBase * 0.11) : 0;
            this.grandTotal = this.taxableBase + this.taxAmount;
        },

        formatRupiah(num) {
            return 'Rp ' + Math.round(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endif

@endsection
