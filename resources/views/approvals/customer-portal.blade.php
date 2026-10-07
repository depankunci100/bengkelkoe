<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Persetujuan Estimasi Servis | {{ \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL') }}</title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .portal-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff;
            padding: 30px 0;
            border-bottom: 4px solid #2563eb;
        }
        .card-summary {
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 0;
        }
    </style>
</head>
<body class="pb-5">

    <!-- Header Portal Publik -->
    <div class="portal-header text-center mb-4">
        <div class="container">
            <span class="badge bg-primary px-3 py-2 text-uppercase mb-2">Portal Persetujuan Pelanggan</span>
            <h4 class="fw-bold mb-1">{{ \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL') }}</h4>
            <p class="small text-white-50 mb-0">{{ \App\Models\WorkshopSetting::get('workshop_address', 'Surabaya, Jawa Timur') }} &bull; Telp: {{ \App\Models\WorkshopSetting::get('workshop_phone', '031-8976543') }}</p>
        </div>
    </div>

    <div class="container" style="max-width: 760px;">

        <!-- Flash Message Alerts -->
        @if(session('success'))
            <div class="alert alert-success shadow-sm d-flex align-items-center gap-3 mb-4">
                <i class="bi bi-check-circle-fill fs-3"></i>
                <div>
                    <h6 class="fw-bold mb-0">Berhasil Disimpan</h6>
                    <small>{{ session('success') }}</small>
                </div>
            </div>
        @endif

        @if($wo->approval_status === 'APPROVED')
            <div class="alert alert-success shadow-sm d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-check-circle-fill fs-4"></i>
                <div>
                    <strong>Pekerjaan Telah Anda Setujui!</strong>
                    <div class="small">Teknisi kami saat ini sedang memproses perbaikan kendaraan Anda. Kami akan memberi tahu Anda saat pengerjaan selesai.</div>
                </div>
            </div>
        @elseif($wo->approval_status === 'REJECTED')
            <div class="alert alert-danger shadow-sm d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-x-circle-fill fs-4"></i>
                <div>
                    <strong>Perbaikan Telah Ditolak.</strong>
                    <div class="small">Anda telah memilih untuk tidak melanjutkan estimasi perbaikan ini. Silakan hubungi bengkel jika ada pertanyaan.</div>
                </div>
            </div>
        @endif

        <!-- Card Informasi Kendaraan & Pelanggan -->
        <div class="card card-summary mb-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold">Nomor Work Order</span>
                        <h5 class="fw-bold text-primary mb-0">{{ $wo->wo_number }}</h5>
                    </div>
                    <span class="badge bg-dark fs-6 px-3 py-2">{{ $wo->vehicle->plate_number }}</span>
                </div>

                <div class="row g-2 small border-top pt-3">
                    <div class="col-6">
                        <span class="text-muted">Pemilik:</span>
                        <strong class="d-block text-dark">{{ $wo->customer->name }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Kendaraan:</span>
                        <strong class="d-block text-dark">{{ $wo->vehicle->brand }} {{ $wo->vehicle->model }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Keluhan Awal:</span>
                        <div class="text-dark">{{ $wo->complaint }}</div>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">Teknisi Pemeriksa:</span>
                        <div class="text-dark">{{ $wo->technician->name ?? 'Mekanik Bengkel' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Hasil Diagnosa & Pemeriksaan Fisik (Inspeksi) -->
        @if($wo->inspection && $wo->inspection->items->isNotEmpty())
            <div class="card card-summary mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-clipboard2-check text-primary fs-5"></i> Hasil Pemeriksaan & Diagnosa Kendaraan
                    </h6>
                    <span class="badge bg-primary-subtle text-primary border">
                        {{ $wo->inspection->items->count() }} Poin Diperiksa
                    </span>
                </div>
                <div class="card-body p-4">
                    @if($wo->inspection->overall_summary)
                        <div class="p-3 bg-light rounded border-start border-4 border-primary mb-3">
                            <span class="text-muted small text-uppercase fw-bold d-block mb-1">
                                <i class="bi bi-chat-left-quote text-primary"></i> Kesimpulan & Rekomendasi Teknisi:
                            </span>
                            <div class="text-dark fw-semibold small">
                                "{{ $wo->inspection->overall_summary }}"
                            </div>
                        </div>
                    @endif

                    <div class="row g-2">
                        @foreach($wo->inspection->items as $insItem)
                            <div class="col-12 col-md-6">
                                <div class="p-2 border rounded bg-white h-100 d-flex flex-column justify-content-between shadow-sm">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <div>
                                            <span class="text-muted text-uppercase d-block" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                                                {{ $insItem->category }}
                                            </span>
                                            <span class="fw-bold text-dark small">{{ $insItem->item_name }}</span>
                                        </div>
                                        <div>
                                            @php
                                                $condBadge = match($insItem->condition) {
                                                    'GOOD' => ['class' => 'bg-success', 'icon' => 'bi-check-circle', 'label' => 'Baik'],
                                                    'WARNING' => ['class' => 'bg-warning text-dark', 'icon' => 'bi-exclamation-triangle', 'label' => 'Perhatian'],
                                                    'BAD' => ['class' => 'bg-danger', 'icon' => 'bi-x-circle', 'label' => 'Rusak'],
                                                    'NEED_REPLACEMENT' => ['class' => 'bg-danger', 'icon' => 'bi-arrow-repeat', 'label' => 'Perlu Ganti'],
                                                    default => ['class' => 'bg-secondary', 'icon' => 'bi-circle', 'label' => $insItem->condition],
                                                };
                                            @endphp
                                            <span class="badge {{ $condBadge['class'] }} d-inline-flex align-items-center gap-1" style="font-size: 0.7rem;">
                                                <i class="bi {{ $condBadge['icon'] }}"></i> {{ $condBadge['label'] }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($insItem->notes)
                                        <div class="small text-muted bg-light p-1 px-2 rounded border" style="font-size: 0.75rem;">
                                            <i class="bi bi-info-circle text-primary me-1"></i> {{ $insItem->notes }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Keputusan Approval (Persetujuan Parsial / Total) -->
        <form action="{{ route('customer.approval.submit', $wo->approval_token) }}" method="POST">
            @csrf

            <!-- Card Rincian Estimasi Biaya -->
            <div class="card card-summary mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-wrench-adjustable text-primary"></i> Rincian Estimasi Jasa & Suku Cadang
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($wo->items as $item)
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1 pe-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge {{ $item->type === 'SERVICE' ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary' }} border" style="font-size: 0.65rem;">
                                                {{ $item->type }}
                                            </span>
                                            <span class="fw-bold text-dark">{{ $item->item_name }}</span>
                                        </div>

                                        @if($item->is_additional)
                                            <div class="badge bg-warning text-dark mt-1" style="font-size: 0.65rem;">
                                                <i class="bi bi-exclamation-triangle"></i> Pekerjaan Tambahan (Hasil Diagnosa)
                                            </div>
                                        @endif

                                        @if($item->notes)
                                            <small class="text-muted d-block mt-1">{{ $item->notes }}</small>
                                        @endif

                                        <div class="small text-muted mt-1">
                                            {{ (float) $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <span class="fw-bold fs-6 text-dark d-block mb-2">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </span>

                                        @if($wo->approval_status === 'PENDING' || $wo->status === 'WAITING_APPROVAL')
                                            <!-- Pilihan Setujui / Tolak Parsial -->
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check" name="items[{{ $item->id }}]" id="approve_{{ $item->id }}" value="APPROVED" checked>
                                                <label class="btn btn-outline-success" for="approve_{{ $item->id }}">Setuju</label>

                                                <input type="radio" class="btn-check" name="items[{{ $item->id }}]" id="reject_{{ $item->id }}" value="REJECTED">
                                                <label class="btn btn-outline-danger" for="reject_{{ $item->id }}">Tolak</label>
                                            </div>
                                        @else
                                            <span class="badge {{ $item->approval_status === 'APPROVED' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $item->approval_status }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Footer Rincian Total -->
                <div class="card-footer bg-light p-3">
                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">Total Jasa:</span>
                        <span class="fw-semibold">Rp {{ number_format($wo->total_services, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">Total Suku Cadang:</span>
                        <span class="fw-semibold">Rp {{ number_format($wo->total_parts, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 small">
                        <span class="text-muted">PPN (11%):</span>
                        <span class="fw-semibold">Rp {{ number_format($wo->tax, 0, ',', '.') }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between py-1">
                        <span class="fw-bold fs-5 text-dark">Total Estimasi:</span>
                        <span class="fw-bold fs-5 text-primary">Rp {{ number_format($wo->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            @if($wo->approval_status === 'PENDING' || $wo->status === 'WAITING_APPROVAL')
                <!-- Tombol Aksi Pelanggan (Section 37) -->
                <div class="card card-summary mb-4">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold text-dark mb-2">Tentukan Persetujuan Anda:</h6>
                        <p class="small text-muted mb-4">Silakan pilih salah satu opsi di bawah ini. Anda dapat menyetujui seluruh pekerjaan, menyetujui sebagian item yang dipilih di atas, atau menolak perbaikan.</p>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <button type="submit" name="decision" value="APPROVE_ALL" class="btn btn-success btn-lg px-4 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check2-circle fs-5"></i> Setujui Seluruh Perbaikan
                            </button>

                            <button type="submit" name="decision" value="PARTIAL" class="btn btn-outline-primary btn-lg px-4 fw-semibold">
                                Simpan Pilihan Item
                            </button>

                            <button type="submit" name="decision" value="REJECT_ALL" class="btn btn-outline-danger btn-lg px-4 fw-semibold" onclick="return confirm('Apakah Anda yakin ingin menolak seluruh perbaikan?')">
                                Tolak Perbaikan
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </form>

        <div class="text-center text-muted small mt-4">
            Jika ada pertanyaan atau konfirmasi via telepon, hubungi hotline bengkel kami di <strong>{{ \App\Models\WorkshopSetting::get('workshop_phone', '031-8976543') }}</strong> atau WhatsApp <strong>{{ \App\Models\WorkshopSetting::get('whatsapp_number', '081234567890') }}</strong>.
        </div>

    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
