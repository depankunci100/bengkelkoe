@extends('layouts.app')

@section('title', 'Data Kendaraan')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Data Kendaraan</h4>
        <p class="text-muted small mb-0">Daftar seluruh kendaraan pelanggan yang terdata pada sistem bengkel.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newVehicleModal">
            <i class="bi bi-plus-circle-fill"></i> Tambah Kendaraan
        </button>
    </div>
</div>
@endsection

@section('content')

<!-- Search Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('vehicles.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari plat nomor, merk, tipe, atau pemilik..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            @if($search)
                <div class="col-auto">
                    <a href="{{ route('vehicles.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<x-card>
    @if($vehicles->isEmpty())
        <x-empty-state title="Tidak Ada Data Kendaraan" description="Belum ada unit mobil yang cocok dengan pencarian." icon="car-front" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Plat Nomor</th>
                        <th>Merk & Model</th>
                        <th>Tahun / Transmisi</th>
                        <th>Pemilik (Customer)</th>
                        <th>Odometer Terakhir</th>
                        <th>Riwayat Servis</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicles as $veh)
                        <tr>
                            <td>
                                <span class="badge bg-dark fs-6 px-3 py-1 fw-bold">{{ $veh->plate_number }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark d-block">{{ $veh->brand }} {{ $veh->model }}</span>
                                <small class="text-muted">Warna: {{ $veh->color ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $veh->year ?? '-' }}</span>
                                <span class="small text-muted d-block">{{ $veh->transmission }}</span>
                            </td>
                            <td>
                                <a href="{{ route('customers.show', $veh->customer_id) }}" class="fw-semibold text-primary text-decoration-none d-block">
                                    {{ $veh->customer->name }}
                                </a>
                                <small class="text-muted">{{ $veh->customer->phone }}</small>
                            </td>
                            <td>
                                <span class="fw-bold"><i class="bi bi-speedometer2 text-muted"></i> {{ number_format($veh->odometer, 0, ',', '.') }}</span> <small class="text-muted">KM</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    {{ $veh->workOrders->count() }} Kali Servis
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('work-orders.create', ['customer_id' => $veh->customer_id, 'vehicle_id' => $veh->id]) }}" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1" title="Buat WO Langsung">
                                    <i class="bi bi-wrench"></i> Servis
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $vehicles->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</x-card>

<!-- Modal Tambah Kendaraan Baru -->
<x-modal id="newVehicleModal" title="Daftarkan Kendaraan Baru">
    <form action="{{ route('vehicles.store') }}" method="POST">
        @csrf
        <x-form.select name="customer_id" label="Pilih Pemilik (Customer)" required>
            <option value="">-- Pilih Customer --</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
            @endforeach
        </x-form.select>

        <x-form.input name="plate_number" label="Nomor Polisi (Plat Nomor)" placeholder="Contoh: L 1234 AB" required icon="card-text" />

        <div class="row">
            <div class="col-md-6">
                <x-form.input name="brand" label="Merk Mobil" placeholder="Toyota / Honda..." required />
            </div>
            <div class="col-md-6">
                <x-form.input name="model" label="Tipe / Model" placeholder="Avanza / Brio..." required />
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <x-form.input name="year" type="number" label="Tahun Pembuatan" placeholder="2022" />
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
                <x-form.input name="color" label="Warna" placeholder="Silver / Hitam..." />
            </div>
            <div class="col-md-6">
                <x-form.input name="odometer" type="number" label="Odometer (KM)" placeholder="30000" />
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Daftarkan Unit</button>
        </div>
    </form>
</x-modal>

@endsection
