@extends('layouts.app')

@section('title', 'Detail Customer - ' . $customer->name)

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">{{ $customer->name }}</h4>
            <span class="text-muted small">
                <i class="bi bi-whatsapp text-success"></i> {{ $customer->phone }}
                @if($customer->email) &bull; <i class="bi bi-envelope"></i> {{ $customer->email }} @endif
            </span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('work-orders.create', ['customer_id' => $customer->id]) }}" class="btn btn-success d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i> Buat WO Baru
        </a>
        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-pencil"></i> Edit Profil
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- NAV TABS (Bootstrap 5) -->
<ul class="nav nav-tabs mb-4 border-bottom" id="customerTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button" role="tab">
            <i class="bi bi-person-lines-fill me-1"></i> Informasi Profil
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles-pane" type="button" role="tab">
            <i class="bi bi-car-front-fill me-1"></i> Kendaraan ({{ $customer->vehicles->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="history-tab" data-bs-toggle="tab" data-bs-target="#history-pane" type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i> Riwayat Servis / WO ({{ $customer->workOrders->count() }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices-pane" type="button" role="tab">
            <i class="bi bi-receipt me-1"></i> Invoice ({{ $customer->invoices->count() }})
        </button>
    </li>
</ul>

<!-- TAB CONTENT -->
<div class="tab-content" id="customerTabsContent">

    <!-- 1. TAB: INFORMASI -->
    <div class="tab-pane fade show active" id="info-pane" role="tabpanel">
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <x-card title="Data Kontak & Domisili" icon="person-vcard">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted small" style="width: 140px;">Nama Lengkap</td>
                            <td class="fw-bold text-dark">{{ $customer->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">No. Handphone / WA</td>
                            <td>
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $customer->phone)) }}" target="_blank" class="text-success fw-semibold text-decoration-none">
                                    <i class="bi bi-whatsapp"></i> {{ $customer->phone }}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Email</td>
                            <td>{{ $customer->email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Alamat</td>
                            <td>{{ $customer->address ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted small">Terdaftar Sejak</td>
                            <td>{{ $customer->created_at->translatedFormat('d F Y, H:i') }}</td>
                        </tr>
                    </table>
                </x-card>
            </div>

            <div class="col-12 col-md-6">
                <x-card title="Catatan Khusus Pelanggan" icon="sticky">
                    @if($customer->notes)
                        <div class="p-3 bg-light rounded text-dark border">
                            {{ $customer->notes }}
                        </div>
                    @else
                        <p class="text-muted small mb-0">Tidak ada catatan preferensi khusus untuk customer ini.</p>
                    @endif
                </x-card>
            </div>
        </div>
    </div>

    <!-- 2. TAB: KENDARAAN -->
    <div class="tab-pane fade" id="vehicles-pane" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0 text-dark">Daftar Unit Terdaftar</h6>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                <i class="bi bi-plus-lg me-1"></i> Tambah Kendaraan
            </button>
        </div>

        @if($customer->vehicles->isEmpty())
            <x-empty-state title="Belum Ada Kendaraan" description="Pelanggan ini belum memiliki kendaraan yang didaftarkan." icon="car-front">
                <x-slot:action>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                        <i class="bi bi-plus-lg me-1"></i> Daftarkan Kendaraan
                    </button>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="row g-3">
                @foreach($customer->vehicles as $veh)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-dark fs-6 px-3 py-2 fw-bold text-tracking">{{ $veh->plate_number }}</span>
                                    <span class="badge bg-light text-muted border">{{ $veh->year }}</span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">{{ $veh->brand }} {{ $veh->model }}</h6>
                                <p class="text-muted small mb-3">
                                    Transmisi: <strong>{{ $veh->transmission }}</strong> &bull; Warna: {{ $veh->color ?? '-' }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <small class="text-muted">
                                        <i class="bi bi-speedometer2"></i> {{ number_format($veh->odometer, 0, ',', '.') }} KM
                                    </small>
                                    <a href="{{ route('work-orders.create', ['customer_id' => $customer->id, 'vehicle_id' => $veh->id]) }}" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-wrench"></i> Servis Unit Ini
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 3. TAB: RIWAYAT SERVIS / WO -->
    <div class="tab-pane fade" id="history-pane" role="tabpanel">
        <x-card>
            @if($customer->workOrders->isEmpty())
                <x-empty-state title="Belum Ada Riwayat Servis" description="Customer ini belum pernah memiliki Work Order servis sebelumnya." icon="clock-history" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>No. WO</th>
                                <th>Kendaraan</th>
                                <th>Keluhan Awal</th>
                                <th>Teknisi</th>
                                <th>Status</th>
                                <th>Total Biaya</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->workOrders as $wo)
                                <tr>
                                    <td>
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="fw-bold text-decoration-none">
                                            {{ $wo->wo_number }}
                                        </a>
                                        <div class="small text-muted">{{ $wo->created_at->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $wo->vehicle->plate_number }}</span>
                                        <div class="small text-muted">{{ $wo->vehicle->brand }} {{ $wo->vehicle->model }}</div>
                                    </td>
                                    <td class="small text-dark" style="max-width: 200px;">
                                        {{ Str::limit($wo->complaint, 60) }}
                                    </td>
                                    <td>
                                        {{ $wo->technician->name ?? 'Belum ditugaskan' }}
                                    </td>
                                    <td>
                                        <x-status-badge :status="$wo->status" />
                                    </td>
                                    <td class="fw-bold">
                                        Rp {{ number_format($wo->grand_total, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-light border">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    <!-- 4. TAB: INVOICE -->
    <div class="tab-pane fade" id="invoices-pane" role="tabpanel">
        <x-card>
            @if($customer->invoices->isEmpty())
                <x-empty-state title="Belum Ada Invoice" description="Belum ada transaksi faktur tagihan untuk pelanggan ini." icon="receipt" />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th>No. Faktur</th>
                                <th>No. WO</th>
                                <th>Tanggal</th>
                                <th>Grand Total</th>
                                <th>Dibayar</th>
                                <th>Status Tagihan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customer->invoices as $inv)
                                <tr>
                                    <td class="fw-bold">{{ $inv->invoice_number }}</td>
                                    <td>
                                        <a href="{{ route('work-orders.show', $inv->work_order_id) }}" class="text-decoration-none">
                                            {{ $inv->workOrder->wo_number }}
                                        </a>
                                    </td>
                                    <td class="small">{{ $inv->created_at->format('d/m/Y') }}</td>
                                    <td class="fw-bold">Rp {{ number_format($inv->grand_total, 0, ',', '.') }}</td>
                                    <td class="text-success fw-semibold">Rp {{ number_format($inv->amount_paid, 0, ',', '.') }}</td>
                                    <td>
                                        <x-status-badge :status="$inv->payment_status" />
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('invoices.show', $inv->id) }}" class="btn btn-sm btn-light border">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-light border">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</div>

<!-- MODAL TAMBAH KENDARAAN CEPAT -->
<x-modal id="addVehicleModal" title="Tambah Kendaraan untuk {{ $customer->name }}">
    <form action="{{ route('vehicles.store') }}" method="POST">
        @csrf
        <input type="hidden" name="customer_id" value="{{ $customer->id }}">

        <x-form.input name="plate_number" label="Nomor Polisi (Plat Nomor)" placeholder="Contoh: L 1234 AB" required icon="card-text" />

        <div class="row">
            <div class="col-md-6">
                <x-form.input name="brand" label="Merk Mobil" placeholder="Toyota / Honda..." required />
            </div>
            <div class="col-md-6">
                <x-form.input name="model" label="Model / Tipe" placeholder="Avanza / Brio..." required />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-form.input name="year" type="number" label="Tahun" placeholder="2021" />
            </div>
            <div class="col-md-6">
                <x-form.select name="transmission" label="Transmisi">
                    <option value="Automatic">Automatic (AT/CVT)</option>
                    <option value="Manual">Manual (MT)</option>
                </x-form.select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-form.input name="color" label="Warna" placeholder="Silver / Putih..." />
            </div>
            <div class="col-md-6">
                <x-form.input name="odometer" type="number" label="Odometer (KM)" placeholder="45000" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Kendaraan</button>
        </div>
    </form>
</x-modal>

@endsection
