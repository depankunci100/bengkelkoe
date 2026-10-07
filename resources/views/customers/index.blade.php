@extends('layouts.app')

@section('title', 'Data Customer')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Data Customer</h4>
        <p class="text-muted small mb-0">Kelola informasi pelanggan bengkel, kontak, dan daftar kendaraan terdaftar.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <i class="bi bi-person-plus-fill"></i>
            <span>Tambah Customer</span>
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- Search Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('customers.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, no. telepon, atau email..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
            @if($search)
                <div class="col-auto">
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

<!-- Table Customer -->
<x-card>
    @if($customers->isEmpty())
        <x-empty-state
            title="Tidak Ada Data Customer"
            description="Tidak ditemukan customer sesuai pencarian Anda atau data belum didaftarkan."
            icon="people"
        >
            <x-slot:action>
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Customer Baru
                </a>
            </x-slot:action>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Customer</th>
                        <th>Nomor Telepon / WA</th>
                        <th>Jumlah Kendaraan</th>
                        <th>Riwayat WO</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $index => $c)
                        <tr>
                            <td class="text-muted small">{{ $customers->firstItem() + $index }}</td>
                            <td>
                                <a href="{{ route('customers.show', $c->id) }}" class="fw-bold text-dark text-decoration-none d-block">
                                    {{ $c->name }}
                                </a>
                                @if($c->email)
                                    <small class="text-muted">{{ $c->email }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $c->phone)) }}" target="_blank" class="text-success text-decoration-none fw-semibold">
                                    <i class="bi bi-whatsapp"></i> {{ $c->phone }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-car-front-fill me-1"></i> {{ $c->vehicles_count }} Kendaraan
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    {{ $c->work_orders_count }} WO
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('customers.show', $c->id) }}" class="btn btn-light border text-primary" title="Detail Customer">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('customers.edit', $c->id) }}" class="btn btn-light border text-dark" title="Edit Data">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="{{ route('work-orders.create', ['customer_id' => $c->id]) }}" class="btn btn-light border text-success" title="Buat Work Order">
                                        <i class="bi bi-plus-circle"></i> Buat WO
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $customers->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</x-card>

@endsection
