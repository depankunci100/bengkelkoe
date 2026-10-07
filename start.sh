#!/bin/bash
# ==============================================================
# Script Menjalankan SIM BENGKEL (Laravel 13 + Bootstrap 5 Web)
# ==============================================================

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd "$DIR"

PHP_BIN="/Users/ameliavirismanda/Library/Application Support/Herd/bin/php"

# Dapatkan IP Lokal untuk akses via HP / Device lain di WiFi yang sama
LOCAL_IP=$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null || echo "127.0.0.1")

echo ""
echo "=============================================================="
echo " 🚗 SIM BENGKEL AUTO SERVICE (Sistem Informasi Manajemen Bengkel)"
echo "=============================================================="
echo "Backend / Web : Laravel 13 + Blade + Bootstrap 5.3 + Icons"
echo "Mobile REST   : /api/v1/* (Ready for Flutter Android & iOS)"
echo "--------------------------------------------------------------"
echo "🌐 Akses Web Laptop/Mac : http://127.0.0.1:8080"
echo "📲 Akses HP / Tablet    : http://${LOCAL_IP}:8080"
echo "--------------------------------------------------------------"
echo "Akun Demo (Tersedia Tombol 1-Klik Login Otomatis):"
echo "  1. Owner (Bambang Wijaya)   : owner@bengkel.com   (Password: password)"
echo "  2. Kasir (Siti Rahma)       : admin@bengkel.com   (Password: password)"
echo "  3. Teknisi (Agus Pratama)   : teknisi@bengkel.com (Password: password)"
echo "--------------------------------------------------------------"
echo "Contoh Portal Approval Customer Publik:"
echo "  👉 http://127.0.0.1:8080/approval/$(sqlite3 database/database.sqlite 'SELECT approval_token FROM work_orders WHERE status=\"WAITING_APPROVAL\" LIMIT 1;' 2>/dev/null || echo 'tok_approval_demo')"
echo "=============================================================="
echo "Menjalankan web server... Tekan CTRL+C untuk berhenti."
echo ""

"$PHP_BIN" artisan serve --host=0.0.0.0 --port=8080
