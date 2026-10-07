@extends('layouts.app')

@section('title', 'Master Kategori Sparepart')

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Master Kategori Suku Cadang</h4>
        <p class="text-muted small mb-0">Pengelompokan sparepart: Mesin, Kaki-kaki & Suspensi, Transmisi, Pengereman, dll.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('parts.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
            <i class="bi bi-arrow-left"></i> Lihat Suku Cadang
        </a>
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Tambah Kategori</span>
        </button>
    </div>
</div>
@endsection

@section('content')

<!-- CATEGORY SUMMARY TILES -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Total Kategori</span>
                <h3 class="fw-bold text-dark my-1">{{ $categories->count() }}</h3>
                <small class="text-muted">Kelompok klasifikasi suku cadang</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card shadow-sm border-0 border-start border-success border-4">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-bold">Total Sparepart Terdaftar</span>
                <h3 class="fw-bold text-success my-1">{{ $categories->sum('parts_count') }}</h3>
                <small class="text-muted">Item suku cadang dalam sistem</small>
            </div>
        </div>
    </div>
</div>

<x-card title="Daftar Kategori Sparepart" icon="tags">
    @if($categories->isEmpty())
        <x-empty-state title="Belum Ada Kategori" description="Klik tombol Tambah Kategori untuk mendaftarkan kelompok suku cadang baru." icon="tags" />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th style="width: 60px;">Icon</th>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi & Contoh Komponen</th>
                        <th class="text-center">Jumlah Item</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                        <tr>
                            <td>
                                <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.25rem;">
                                    <i class="bi {{ $cat->icon ?: 'bi-gear' }}"></i>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-dark font-monospace">{{ $cat->code }}</span>
                            </td>
                            <td>
                                <a href="{{ route('parts.index', ['category' => $cat->name]) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $cat->name }}
                                </a>
                            </td>
                            <td>
                                <small class="text-muted">{{ $cat->description ?: '-' }}</small>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('parts.index', ['category' => $cat->name]) }}" class="badge bg-light text-primary border text-decoration-none px-2 py-1 fs-6">
                                    {{ $cat->parts_count }} item
                                </a>
                            </td>
                            <td>
                                @if($cat->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('parts.index', ['category' => $cat->name]) }}" class="btn btn-light border" title="Lihat Suku Cadang">
                                        <i class="bi bi-box-seam"></i>
                                    </a>
                                    <button type="button" class="btn btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $cat->id }}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('part-categories.destroy', $cat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border text-danger" title="Hapus" {{ $cat->parts_count > 0 ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- MODAL EDIT KATEGORI -->
                        <div class="modal fade" id="editModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('part-categories.update', $cat->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Kategori: {{ $cat->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Kode Kategori</label>
                                                <input type="text" name="code" class="form-control" value="{{ $cat->code }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nama Kategori</label>
                                                <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Icon Bootstrap (contoh: bi-gear, bi-wrench, bi-cpu)</label>
                                                <input type="text" name="icon" class="form-control" value="{{ $cat->icon }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Deskripsi</label>
                                                <textarea name="description" class="form-control" rows="2">{{ $cat->description }}</textarea>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active" id="active{{ $cat->id }}" value="1" {{ $cat->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="active{{ $cat->id }}">Status Aktif</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>

<!-- MODAL TAMBAH KATEGORI BARU -->
<div class="modal fade" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('part-categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Tambah Kategori Suku Cadang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode Kategori</label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="Contoh: CAT-SUS / CAT-ENG" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Kaki-Kaki & Suspensi / Mesin" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Icon Bootstrap</label>
                        <select name="icon" class="form-select">
                            <option value="bi-gear">bi-gear (Umum / Mesin)</option>
                            <option value="bi-wrench">bi-wrench (Perkakas / Servis)</option>
                            <option value="bi-arrows-collapse">bi-arrows-collapse (Kaki-kaki / Suspensi)</option>
                            <option value="bi-shuffle">bi-shuffle (Transmisi / Girboks)</option>
                            <option value="bi-disc">bi-disc (Rem / Cakram)</option>
                            <option value="bi-lightning-charge">bi-lightning-charge (Kelistrikan / Aki)</option>
                            <option value="bi-droplet-half">bi-droplet-half (Oli & Fluida)</option>
                            <option value="bi-snow">bi-snow (AC & Pendingin)</option>
                            <option value="bi-funnel">bi-funnel (Filter)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi & Contoh Komponen</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Komponen: Shockbreaker, Balljoint, Tie Rod, Bushing Arm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
