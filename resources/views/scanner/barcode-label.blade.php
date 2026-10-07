<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Barcode - {{ $part->part_number }}</title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .label-container {
            width: 70mm;
            height: 40mm;
            border: 1px dashed #94a3b8;
            padding: 2mm 3mm;
            background: #fff;
            margin: 10px auto;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .barcode-svg {
            max-width: 100%;
            height: 18mm;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .label-container {
                border: none;
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4">
        <div class="d-inline-flex gap-2 align-items-center bg-dark text-white p-2 px-3 rounded shadow">
            <span><strong>Cetak Label Barcode</strong> (Ukuran Standar Rak / Dus: 70 x 40 mm)</span>
            <button onclick="window.print()" class="btn btn-sm btn-primary">
                <i class="bi bi-printer-fill me-1"></i> Cetak Label
            </button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">
                Tutup
            </button>
        </div>
    </div>

    <!-- PREVIEW 1 LABEL / LEMBAR CETAK -->
    <div class="d-flex flex-wrap justify-content-center gap-3">
        @for($i = 0; $i < 4; $i++)
            <div class="label-container shadow-sm">
                <!-- TOP HEADER -->
                <div class="d-flex justify-content-between align-items-center border-bottom pb-1" style="font-size: 8pt;">
                    <span class="fw-bold text-truncate">{{ \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL') }}</span>
                    <span class="badge bg-dark py-0 px-1" style="font-size: 7pt;">RAK: {{ $part->location ?? '-' }}</span>
                </div>

                <!-- PART NAME -->
                <div class="my-1 text-center">
                    <div class="fw-bold text-truncate" style="font-size: 9pt;" title="{{ $part->name }}">{{ $part->name }}</div>
                    <div class="text-muted" style="font-size: 7.5pt;">{{ $part->brand }} | {{ $part->partCategory ? $part->partCategory->name : $part->category }}</div>
                </div>

                <!-- BARCODE SVG -->
                <div class="text-center my-0">
                    <svg id="barcode-{{ $i }}" class="barcode-svg"></svg>
                </div>

                <!-- FOOTER -->
                <div class="d-flex justify-content-between align-items-center border-top pt-1 text-muted" style="font-size: 7pt;">
                    <span>Harga: <strong>Rp {{ number_format($part->selling_price, 0, ',', '.') }}</strong></span>
                    <span class="font-monospace fw-bold text-dark">{{ $part->part_number }}</span>
                </div>
            </div>
        @endfor
    </div>

    <!-- JsBarcode library for crisp SVG vector barcodes -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const code = "{{ $part->effective_barcode }}";
            for (let i = 0; i < 4; i++) {
                JsBarcode(`#barcode-${i}`, code, {
                    format: "CODE128",
                    width: 1.5,
                    height: 38,
                    displayValue: true,
                    fontSize: 10,
                    margin: 0
                });
            }
        });
    </script>
</body>
</html>
