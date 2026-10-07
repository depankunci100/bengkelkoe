@extends('layouts.app')

@section('title', 'Pemeriksaan Kendaraan - ' . $wo->wo_number)

@section('header')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">Inspeksi Kendaraan: {{ $wo->vehicle->plate_number }}</h4>
                <span class="badge bg-dark">{{ $wo->vehicle->brand }} {{ $wo->vehicle->model }}</span>
            </div>
            <small class="text-muted">No. Work Order: <strong>{{ $wo->wo_number }}</strong> &bull; Mekanik: {{ $inspection->technician->name ?? 'Belum ditentukan' }}</small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addItemCheckModal">
            <i class="bi bi-plus-lg me-1"></i> Tambah Poin Cek
        </button>
        <a href="{{ route('work-orders.show', $wo->id) }}" class="btn btn-light border btn-sm">
            <i class="bi bi-file-earmark-medical me-1"></i> Kembali ke WO
        </a>
    </div>
</div>
@endsection

@section('content')

<!-- Ringkasan Keluhan Masuk -->
<div class="alert alert-info border-info shadow-sm mb-4">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
        <div>
            <strong class="d-block">Panduan Keluhan Masuk dari Pelanggan:</strong>
            <span>"{{ $wo->complaint }}"</span>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- SISI KIRI: ACCORDION PEMERIKSAAN KENDARAAN (Section 14) -->
    <div class="col-12 col-lg-8">
        
        <div class="accordion shadow-sm border-0 mb-4" id="inspectionAccordion">
            @php
                $catMeta = [
                    'ENGINE' => ['title' => 'ENGINE (Sistem Mesin & Pelumasan)', 'icon' => 'cpu'],
                    'BRAKE' => ['title' => 'BRAKE (Sistem Pengereman)', 'icon' => 'disc'],
                    'ELECTRICAL' => ['title' => 'ELECTRICAL (Kelistrikan & Battery)', 'icon' => 'lightning-charge'],
                    'SUSPENSION' => ['title' => 'SUSPENSION (Kaki-kaki & Kemudi)', 'icon' => 'gear-wide-connected'],
                    'BODY_INTERIOR' => ['title' => 'BODY & INTERIOR (AC & Kabin)', 'icon' => 'snow'],
                ];
            @endphp

            @foreach($catMeta as $catKey => $catInfo)
                @php
                    $itemsInCat = $groupedItems->get($catKey, collect());
                    $collapseId = 'collapse_' . strtolower($catKey);
                @endphp
                <div class="accordion-item border mb-2 rounded overflow-hidden">
                    <h2 class="accordion-header" id="heading_{{ $collapseId }}">
                        <button class="accordion-button fw-bold text-dark py-3 {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}">
                            <i class="bi bi-{{ $catInfo['icon'] }} me-2 text-primary fs-5"></i>
                            <span class="flex-grow-1">{{ $catInfo['title'] }}</span>
                            <span class="badge bg-secondary rounded-pill me-2" style="font-size: 0.75rem;">
                                {{ $itemsInCat->count() }} Poin
                            </span>
                        </button>
                    </h2>
                    <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#inspectionAccordion">
                        <div class="accordion-body p-0">
                            @if($itemsInCat->isEmpty())
                                <p class="text-muted small p-3 mb-0">Belum ada checklist pada kategori ini.</p>
                            @else
                                <ul class="list-group list-group-flush">
                                    @foreach($itemsInCat as $item)
                                        <li class="list-group-item p-3">
                                            <form action="{{ route('inspections.update-item', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                                    <div>
                                                        <span class="fw-bold text-dark fs-6">{{ $item->item_name }}</span>
                                                        @if($item->notes)
                                                            <div class="small text-muted fst-italic mt-1">
                                                                <i class="bi bi-chat-left-text"></i> {{ $item->notes }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Condition Selector Radios -->
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @foreach(['GOOD' => ['label' => 'GOOD', 'btn' => 'success'], 'WARNING' => ['label' => 'WARNING', 'btn' => 'warning'], 'BAD' => ['label' => 'BAD', 'btn' => 'danger'], 'NEED_REPLACEMENT' => ['label' => 'REPLACE', 'btn' => 'danger']] as $cKey => $cMeta)
                                                            <input type="radio" class="btn-check" name="condition" id="cond_{{ $item->id }}_{{ $cKey }}" value="{{ $cKey }}" {{ $item->condition === $cKey ? 'checked' : '' }} onchange="this.form.submit()">
                                                            <label class="btn btn-outline-{{ $cMeta['btn'] }}" for="cond_{{ $item->id }}_{{ $cKey }}">
                                                                {{ $cMeta['label'] }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <!-- Edit Catatan Box -->
                                                <div class="input-group input-group-sm mt-2">
                                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-pencil"></i></span>
                                                    <input type="text" name="notes" class="form-control border-start-0" placeholder="Tambah catatan teknis temuan..." value="{{ $item->notes }}">
                                                    <button type="submit" class="btn btn-outline-secondary">Simpan Catatan</button>
                                                </div>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <!-- SISI KANAN: RINGKASAN & FORM SELESAI INSPEKSI -->
    <div class="col-12 col-lg-4">
        
        <x-card title="Penyelesaian Diagnosa & Rekomendasi" icon="check2-square">
            <form action="{{ route('inspections.complete', $inspection->id) }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-uppercase">Status Lembar Saat Ini</label>
                    <div>
                        @if($inspection->status === 'COMPLETED')
                            <span class="badge bg-success fs-6 px-3 py-2">
                                <i class="bi bi-check-circle-fill me-1"></i> Telah Selesai
                            </span>
                        @else
                            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                                <i class="bi bi-clock me-1"></i> Sedang Diperiksa
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3">
                    <label for="overall_summary" class="form-label fw-semibold">Kesimpulan & Rekomendasi Teknisi</label>
                    <textarea name="overall_summary" id="overall_summary" rows="5" class="form-control" placeholder="Tuliskan rangkuman temuan kerusakan utama dan sparepart/jasa yang direkomendasikan untuk estimasi pelanggan..." required>{{ old('overall_summary', $inspection->overall_summary) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-check-circle-fill"></i> Simpan & Selesaikan Inspeksi
                </button>
            </form>
        </x-card>

        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-lightbulb text-warning"></i> Panduan Penilaian Kondisi:</h6>
                <ul class="list-unstyled small text-muted mb-0 d-flex flex-column gap-1">
                    <li><strong class="text-success">GOOD:</strong> Komponen layak pakai, tidak perlu tindakan.</li>
                    <li><strong class="text-warning">WARNING:</strong> Ada tanda keausan ringan, perlu pantauan.</li>
                    <li><strong class="text-danger">BAD:</strong> Komponen rusak/tidak berfungsi normal.</li>
                    <li><strong class="text-danger">REPLACE:</strong> Wajib diganti segera demi keamanan berkendara.</li>
                </ul>
            </div>
        </div>

    </div>

</div>

<!-- MODAL TAMBAH POIN CEK -->
<x-modal id="addItemCheckModal" title="Tambah Poin Pemeriksaan Baru">
    <form action="{{ route('inspections.add-item', $inspection->id) }}" method="POST">
        @csrf
        
        <x-form.select name="category" label="Kategori Sistem" required>
            <option value="ENGINE">ENGINE (Sistem Mesin)</option>
            <option value="BRAKE">BRAKE (Sistem Pengereman)</option>
            <option value="ELECTRICAL">ELECTRICAL (Kelistrikan)</option>
            <option value="SUSPENSION">SUSPENSION (Kaki-kaki & Ban)</option>
            <option value="BODY_INTERIOR">BODY_INTERIOR (AC & Kabin)</option>
        </x-form.select>

        <x-form.input name="item_name" label="Nama Komponen / Poin Cek" placeholder="Contoh: Belt Alternator / Minyak Kopling" required />

        <x-form.select name="condition" label="Kondisi Awal" required>
            <option value="GOOD">GOOD (Baik)</option>
            <option value="WARNING">WARNING (Perhatian)</option>
            <option value="BAD">BAD (Rusak)</option>
            <option value="NEED_REPLACEMENT">NEED REPLACEMENT (Perlu Diganti)</option>
        </x-form.select>

        <x-form.textarea name="notes" label="Catatan Awal (Opsional)" placeholder="Keterangan temuan..." rows="2" />

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Tambahkan Poin</button>
        </div>
    </form>
</x-modal>

@endsection
