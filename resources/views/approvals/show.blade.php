@extends('layouts.app')

@section('title', 'Detail Approval - ' . $wo->wo_number)

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('approvals.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">Rincian Approval: {{ $wo->wo_number }}</h4>
                <x-status-badge :status="$wo->status" class="fs-6 px-3 py-1" />
            </div>
            <small class="text-muted">Pelanggan: {{ $wo->customer->name }} &bull; Unit: {{ $wo->vehicle->plate_number }}</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if($wo->status === 'WAITING_APPROVAL')
            <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-success d-inline-flex align-items-center gap-2">
                <i class="bi bi-whatsapp"></i>
                <span>KIRIM ULANG WHATSAPP</span>
            </a>
        @endif
        <a href="{{ url('/approval/' . $wo->approval_token) }}" target="_blank" class="btn btn-outline-dark d-inline-flex align-items-center gap-2">
            <i class="bi bi-box-arrow-up-right"></i>
            <span>Buka Link Pelanggan</span>
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- STATUS ALERTS (Section 16) -->
@if($wo->approval_status === 'APPROVED')
    <div class="alert alert-success d-flex align-items-center gap-2 shadow-sm mb-4">
        <i class="bi bi-check-circle-fill fs-4"></i>
        <div>
            <h6 class="fw-bold mb-0">Customer telah menyetujui pekerjaan.</h6>
            <small>Disetujui pada: {{ $wo->approved_at ? $wo->approved_at->translatedFormat('d F Y, H:i') : '-' }}</small>
        </div>
    </div>
@elseif($wo->approval_status === 'REJECTED')
    <div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm mb-4">
        <i class="bi bi-x-circle-fill fs-4"></i>
        <div>
            <h6 class="fw-bold mb-0">Customer menolak pekerjaan.</h6>
            <small>Pengerjaan dibatalkan atau ditunda sesuai permintaan customer.</small>
        </div>
    </div>
@elseif($wo->status === 'WAITING_APPROVAL')
    <div class="alert alert-warning d-flex align-items-center gap-2 shadow-sm mb-4">
        <i class="bi bi-clock-history fs-4"></i>
        <div>
            <h6 class="fw-bold mb-0">Menunggu Respon Pelanggan</h6>
            <small>Pesan estimasi telah dikirim. Menunggu pelanggan membuka tautan dan menyetujui biaya.</small>
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Kolom Kiri: Rincian Lengkap -->
    <div class="col-12 col-lg-8">
        
        <!-- Informasi Customer & Kendaraan -->
        <x-card title="Informasi Unit & Pelanggan" icon="person-lines-fill" class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <span class="text-muted small text-uppercase fw-bold">Pelanggan</span>
                    <h6 class="fw-bold text-dark mb-1">{{ $wo->customer->name }}</h6>
                    <div class="text-success small fw-semibold"><i class="bi bi-whatsapp"></i> {{ $wo->customer->phone }}</div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small text-uppercase fw-bold">Kendaraan</span>
                    <h6 class="fw-bold text-dark mb-1">{{ $wo->vehicle->brand }} {{ $wo->vehicle->model }}</h6>
                    <span class="badge bg-dark fs-7">{{ $wo->vehicle->plate_number }}</span>
                </div>
            </div>
            <hr class="my-3">
            <div>
                <span class="text-muted small text-uppercase fw-bold d-block mb-1">Keluhan Pelanggan</span>
                <p class="mb-0 text-dark small bg-light p-2 rounded border">{{ $wo->complaint }}</p>
            </div>
        </x-card>

        <!-- Hasil Diagnosa Inspeksi -->
        @if($wo->inspection && $wo->inspection->items->isNotEmpty())
            <x-card title="Hasil Diagnosa Inspeksi" icon="search" class="mb-4">
                <div class="row g-2">
                    @foreach($wo->inspection->items as $it)
                        <div class="col-12 col-md-6">
                            <div class="p-2 border rounded d-flex justify-content-between align-items-center bg-light">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">{{ $it->category }}</small>
                                    <span class="fw-semibold small">{{ $it->item_name }}</span>
                                </div>
                                <x-status-badge :status="$it->condition" style="font-size: 0.72rem;" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        <!-- Rincian Item Pekerjaan & Sparepart (Section 18) -->
        <x-card title="Rincian Item & Status Persetujuan" icon="list-check">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase text-muted">
                        <tr>
                            <th>Item Deskripsi</th>
                            <th>Tipe</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga</th>
                            <th class="text-end">Subtotal</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wo->items as $item)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-dark d-block">{{ $item->item_name }}</span>
                                    @if($item->is_additional)
                                        <span class="badge bg-warning text-dark" style="font-size: 0.65rem;">Pekerjaan Tambahan</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item->type === 'SERVICE' ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary' }} border">
                                        {{ $item->type }}
                                    </span>
                                </td>
                                <td class="text-center">{{ (float) $item->quantity }}</td>
                                <td class="text-end small">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    @if($item->approval_status === 'APPROVED')
                                        <span class="badge bg-success">APPROVED</span>
                                    @elseif($item->approval_status === 'REJECTED')
                                        <span class="badge bg-danger">REJECTED</span>
                                    @else
                                        <span class="badge bg-warning text-dark">PENDING</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 bg-light border-top mt-3">
                <div class="row justify-content-end">
                    <div class="col-12 col-md-6 col-lg-5">
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-semibold">Rp {{ number_format($wo->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">PPN (11%):</span>
                            <span class="fw-semibold">Rp {{ number_format($wo->tax, 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-1">
                        <div class="d-flex justify-content-between py-1">
                            <span class="fw-bold text-dark">Grand Total:</span>
                            <span class="fw-bold text-primary fs-6">Rp {{ number_format($wo->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

    </div>

    <!-- Kolom Kanan: Log Pengiriman -->
    <div class="col-12 col-lg-4">
        <x-card title="Riwayat Komunikasi" icon="chat-left-dots">
            @if($wo->whatsappLogs->isEmpty())
                <p class="text-muted small text-center my-3">Belum ada riwayat pesan dikirim.</p>
            @else
                <div class="d-flex flex-column gap-2">
                    @foreach($wo->whatsappLogs as $wl)
                        <div class="p-2 border rounded bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-success">TERKIRIM</span>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $wl->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <small class="text-dark d-block">{{ $wl->message }}</small>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</div>

@endsection
