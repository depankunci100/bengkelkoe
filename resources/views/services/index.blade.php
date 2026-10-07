@extends('layouts.app')

@section('title', 'Daftar Jasa & Tarif Servis')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Daftar Jasa & Ongkos Kerja</h4>
        <p class="text-muted small mb-0">Kelola master tarif jasa pengerjaan, kategori servis, dan estimasi waktu kerja.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newServiceModal">
            <i class="bi bi-plus-circle-fill"></i> Tambah Jasa Baru
        </button>
    </div>
</div>
@endsection

@section('content')

<!-- Search & Filter -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('services.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama atau kode jasa..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <select name="category" class="form-select">
                    <option value="">-- Semua Kategori Jasa --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            </div>
            @if($search || $category)
                <div class="col-auto">
                    <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            @endif
        </form>
    </div>
</div>

<x-card>
    @if($services->isEmpty())
        <x-empty-state title="Belum Ada Jasa" description="Belum ada data tarif jasa pengerjaan yang tersedia." icon="wrench" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th>Kode</th>
                        <th>Nama Layanan Jasa</th>
                        <th>Kategori</th>
                        <th>Estimasi Waktu</th>
                        <th class="text-end">Tarif Biaya (Rp)</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $svc)
                        <tr>
                            <td><span class="badge bg-dark font-monospace">{{ $svc->code }}</span></td>
                            <td>
                                <span class="fw-bold text-dark d-block">{{ $svc->name }}</span>
                                <small class="text-muted">{{ $svc->description ?? '-' }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $svc->category }}</span></td>
                            <td><i class="bi bi-stopwatch text-muted"></i> {{ $svc->estimated_minutes }} Menit</td>
                            <td class="text-end fw-bold text-primary fs-6">
                                Rp {{ number_format($svc->price, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editModal{{ $svc->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Edit Jasa -->
                        <x-modal id="editModal{{ $svc->id }}" title="Edit Jasa: {{ $svc->name }}">
                            <form action="{{ route('services.update', $svc->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <x-form.input name="code" label="Kode Layanan" :value="$svc->code" required />
                                <x-form.input name="name" label="Nama Jasa" :value="$svc->name" required />
                                <x-form.input name="category" label="Kategori" :value="$svc->category" required />
                                <x-form.input name="price" type="number" label="Tarif (Rp)" :value="$svc->price" required />
                                <x-form.input name="estimated_minutes" type="number" label="Estimasi Menit" :value="$svc->estimated_minutes" required />
                                <x-form.textarea name="description" label="Deskripsi (Opsional)" :value="$svc->description" rows="2" />
                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </x-modal>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($services->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $services->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</x-card>

<!-- Modal Tambah Jasa Baru -->
<x-modal id="newServiceModal" title="Tambah Layanan Jasa Baru">
    <form action="{{ route('services.store') }}" method="POST">
        @csrf
        <x-form.input name="code" label="Kode Layanan (Unik)" placeholder="Contoh: SRV-009" required />
        <x-form.input name="name" label="Nama Jasa Lengkap" placeholder="Contoh: Kuras Minyak Rem 4 Roda" required />
        <x-form.input name="category" label="Kategori Servis" placeholder="Perawatan Berkala / Mesin / Rem / AC" required />
        <div class="row">
            <div class="col-6">
                <x-form.input name="price" type="number" label="Tarif (Rp)" placeholder="150000" required />
            </div>
            <div class="col-6">
                <x-form.input name="estimated_minutes" type="number" label="Estimasi Durasi (Menit)" value="45" required />
            </div>
        </div>
        <x-form.textarea name="description" label="Deskripsi Pekerjaan (Opsional)" placeholder="Uraian prosedur pekerjaan..." rows="2" />
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Daftarkan Jasa</button>
        </div>
    </form>
</x-modal>

@endsection
