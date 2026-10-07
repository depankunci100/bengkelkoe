@extends('layouts.app')

@section('title', 'Master Supplier Pemasok')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Master Supplier (Pemasok)</h4>
        <p class="text-muted small mb-0">Kelola direktori vendor, distributor suku cadang, dan riwayat pasokan barang.</p>
    </div>
    <div>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Tambah Supplier</span>
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- KPI KARTU SUPPLIER -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-truck"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Total Supplier</span>
                    <h3 class="fw-bold text-dark my-0">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Supplier Aktif</span>
                    <h3 class="fw-bold text-success my-0">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-bold">Part Tertaut</span>
                    <h3 class="fw-bold text-dark my-0">{{ $stats['total_parts_supplied'] }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SEARCH & FILTER -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('suppliers.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama supplier, kode, kontak, atau telepon..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Non-Aktif</option>
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>

            @if($search || $status !== null)
                <div class="col-auto">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            @endif
        </form>
    </div>
</div>

<x-card>
    @if($suppliers->isEmpty())
        <x-empty-state title="Belum Ada Supplier" description="Tidak ada data supplier yang ditemukan sesuai pencarian Anda." icon="truck" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Supplier & Perusahaan</th>
                        <th>Kontak Person</th>
                        <th>Telepon / WA</th>
                        <th>Kota / Alamat</th>
                        <th class="text-center">Suku Cadang</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $sup)
                        <tr>
                            <td>
                                <span class="badge bg-dark font-monospace">{{ $sup->code }}</span>
                            </td>
                            <td>
                                <a href="{{ route('suppliers.show', $sup->id) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $sup->name }}
                                </a>
                                @if($sup->email)
                                    <small class="text-muted d-block">{{ $sup->email }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="text-dark">{{ $sup->contact_person ?? '-' }}</span>
                            </td>
                            <td>
                                @if($sup->phone)
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $sup->phone);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-sm btn-outline-success py-0 px-2 d-inline-flex align-items-center gap-1 text-decoration-none">
                                        <i class="bi bi-whatsapp"></i> {{ $sup->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 200px;">
                                    {{ $sup->address ?? '-' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border fw-bold">{{ $sup->parts_count }} item</span>
                            </td>
                            <td>
                                @if($sup->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('suppliers.show', $sup->id) }}" class="btn btn-light border" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('suppliers.edit', $sup->id) }}" class="btn btn-light border text-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('suppliers.destroy', $sup->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus atau menonaktifkan supplier ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border text-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $suppliers->links() }}
        </div>
    @endif
</x-card>
@endsection
