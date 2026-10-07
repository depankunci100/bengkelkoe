@extends('layouts.print')

@section('title', 'Bukti Pengeluaran Barang - ' . $workOrder->wo_number)

@section('content')
<div class="picking-slip-document">
    <!-- KOP BENGKEL -->
    <div class="row align-items-center border-bottom pb-3 mb-4">
        <div class="col-8">
            <h3 class="fw-bold text-primary mb-1">{{ \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL') }}</h3>
            <p class="text-muted small mb-0">{{ \App\Models\WorkshopSetting::get('workshop_address', 'Jl. Raya Bengkel No. 123, Surabaya') }}</p>
            <p class="text-muted small mb-0">Telp/WA: {{ \App\Models\WorkshopSetting::get('workshop_phone', '0812-3456-7890') }} | Email: info@bengkel.com</p>
        </div>
        <div class="col-4 text-end">
            <div class="border border-dark p-2 text-center rounded">
                <span class="small fw-bold text-uppercase d-block text-muted">Surat Jalan / Picking Slip</span>
                <span class="fs-6 fw-bold font-monospace text-dark">{{ $workOrder->wo_number }}</span>
            </div>
        </div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="text-center mb-4">
        <h4 class="fw-bold text-uppercase mb-1">BUKTI PENGELUARAN SUKU CADANG</h4>
        <p class="text-muted small mb-0">Dokumen Verifikasi Pengeluaran Barang dari Gudang ke Teknisi</p>
    </div>

    <!-- INFORMASI WORK ORDER & PELANGGAN -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="card bg-light border-0 p-3 h-100">
                <h6 class="fw-bold border-bottom pb-1 mb-2 text-secondary">DATA WORK ORDER & KENDARAAN</h6>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted" style="width: 130px;">No. Work Order</td>
                        <td class="fw-bold font-monospace">: {{ $workOrder->wo_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal WO</td>
                        <td>: {{ $workOrder->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Polisi Kendaraan</td>
                        <td class="fw-bold">: {{ $workOrder->vehicle->license_plate }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kendaraan</td>
                        <td>: {{ $workOrder->vehicle->brand }} {{ $workOrder->vehicle->model }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Teknisi Bertugas</td>
                        <td class="fw-bold text-primary">: {{ $workOrder->technician ? $workOrder->technician->name : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col-6">
            <div class="card bg-light border-0 p-3 h-100">
                <h6 class="fw-bold border-bottom pb-1 mb-2 text-secondary">DATA PELANGGAN & VERIFIKASI</h6>
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted" style="width: 130px;">Nama Pelanggan</td>
                        <td class="fw-bold">: {{ $workOrder->customer->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. HP / WA</td>
                        <td>: {{ $workOrder->customer->phone }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Waktu Verifikasi</td>
                        <td>: {{ $workOrder->parts_verified_at ? $workOrder->parts_verified_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Petugas Verifier</td>
                        <td class="fw-bold">: {{ $workOrder->partsVerifier ? $workOrder->partsVerifier->name : auth()->user()->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Pengeluaran</td>
                        <td>: 
                            @if($workOrder->allApprovedPartsVerified())
                                <span class="badge bg-success text-uppercase">Terverifikasi Lengkap</span>
                            @else
                                <span class="badge bg-warning text-dark text-uppercase">Verifikasi Sebagian</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- TABEL DAFTAR SUKU CADANG YANG DIKELUARKAN -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle small">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 40px;">No</th>
                    <th style="width: 140px;">Part Number</th>
                    <th>Nama Suku Cadang</th>
                    <th style="width: 80px;">Rak</th>
                    <th style="width: 70px;">SPK</th>
                    <th style="width: 80px;">Dikeluarkan</th>
                    <th style="width: 110px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @forelse($workOrder->items->where('type', 'PART') as $item)
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="font-monospace fw-bold">{{ $item->part ? $item->part->part_number : '-' }}</td>
                        <td>
                            <div class="fw-semibold">{{ $item->item_name }}</div>
                            <small class="text-muted">{{ $item->part ? $item->part->brand : '' }}</small>
                        </td>
                        <td class="text-center">{{ $item->part ? ($item->part->location ?? '-') : '-' }}</td>
                        <td class="text-center fw-bold">{{ (float)$item->quantity }} {{ $item->part->unit ?? 'Pcs' }}</td>
                        <td class="text-center fw-bold text-success">{{ (float)$item->verified_quantity }} {{ $item->part->unit ?? 'Pcs' }}</td>
                        <td class="text-center">
                            @if($item->isFullyVerified())
                                <span class="badge bg-success">TERVERIFIKASI</span>
                            @else
                                <span class="badge bg-warning text-dark">SEBAGIAN</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada item suku cadang pada Work Order ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="alert alert-secondary py-2 px-3 small mb-4">
        <i class="bi bi-info-circle me-1"></i> <strong>Catatan:</strong> Suku cadang yang tercantum di atas telah melalui proses pemindaian barcode fisik dan diserahkan dalam kondisi baik untuk pengerjaan servis unit kendaraan bersangkutan.
    </div>

    <!-- KOLOM TANDA TANGAN 3 PIHAK -->
    <div class="row text-center mt-5 pt-3">
        <div class="col-4">
            <p class="small text-muted mb-5">Yang Menyerahkan (Petugas Gudang),</p>
            <div class="border-bottom mx-auto" style="width: 75%;"></div>
            <p class="fw-bold small mt-1 mb-0">{{ $workOrder->partsVerifier ? $workOrder->partsVerifier->name : auth()->user()->name }}</p>
        </div>
        <div class="col-4">
            <p class="small text-muted mb-5">Yang Menerima (Teknisi),</p>
            <div class="border-bottom mx-auto" style="width: 75%;"></div>
            <p class="fw-bold small mt-1 mb-0">{{ $workOrder->technician ? $workOrder->technician->name : 'Teknisi Bertugas' }}</p>
        </div>
        <div class="col-4">
            <p class="small text-muted mb-5">Mengetahui (Kepala Bengkel / SA),</p>
            <div class="border-bottom mx-auto" style="width: 75%;"></div>
            <p class="fw-bold small mt-1 mb-0">{{ \App\Models\WorkshopSetting::get('owner_name', 'Bambang Wijaya') }}</p>
        </div>
    </div>
</div>
@endsection
