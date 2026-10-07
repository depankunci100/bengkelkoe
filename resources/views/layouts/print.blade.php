<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cetak Faktur / Dokumen')</title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .print-page {
            max-width: 800px;
            margin: 20px auto;
            background: #ffffff;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        @media print {
            body {
                background: #ffffff;
            }
            .print-page {
                max-width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="no-print bg-dark text-white py-2 px-3 text-center sticky-top d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-printer me-2"></i> Mode Cetak Dokumen SIM BENGKEL
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-printer-fill"></i> Cetak Sekarang
            </button>
            <button onclick="window.close()" class="btn btn-sm btn-outline-light">
                Tutup
            </button>
        </div>
    </div>

    <div class="print-page">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
