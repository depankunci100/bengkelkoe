@php
    $user = auth()->user();
    $role = $user?->role ?? 'admin';
@endphp

<div class="nav flex-column">

    <!-- MENU UTAMA -->
    <div class="sidebar-heading">Menu Utama</div>

    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>

    <!-- WORK ORDER & OPERASIONAL -->
    <div class="sidebar-heading">Operasional Bengkel</div>

    <a href="{{ route('work-orders.index') }}" class="nav-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-medical"></i>
        <span>Work Order (WO)</span>
    </a>

    <a href="{{ route('inspections.index') }}" class="nav-link {{ request()->routeIs('inspections.*') ? 'active' : '' }}">
        <i class="bi bi-clipboard2-check"></i>
        <span>Inspeksi Kendaraan</span>
    </a>

    <a href="{{ route('approvals.index') }}" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
        <i class="bi bi-whatsapp"></i>
        <span>Approval Pelanggan</span>
    </a>

    <a href="{{ route('scanner.index') }}" class="nav-link {{ request()->routeIs('scanner.*') ? 'active' : '' }}">
        <i class="bi bi-upc-scan"></i>
        <span>Scanner Barcode</span>
        <span class="badge bg-primary ms-auto" style="font-size: 0.65rem;">Gudang</span>
    </a>

    <!-- MASTER DATA (Owner & Admin) -->
    @if($role === 'owner' || $role === 'admin')
        <div class="sidebar-heading">Pelanggan & Unit</div>

        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            <span>Customer</span>
        </a>

        <a href="{{ route('vehicles.index') }}" class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}">
            <i class="bi bi-car-front"></i>
            <span>Kendaraan</span>
        </a>

        <!-- INVENTORY (Owner & Admin) -->
        <div class="sidebar-heading">Gudang & Layanan</div>

        <a href="{{ route('parts.index') }}" class="nav-link {{ request()->routeIs('parts.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i>
            <span>Sparepart & Stok</span>
        </a>

        <a href="{{ route('part-categories.index') }}" class="nav-link {{ request()->routeIs('part-categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i>
            <span>Kategori Sparepart</span>
        </a>

        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <i class="bi bi-truck"></i>
            <span>Master Supplier</span>
        </a>

        <a href="{{ route('supplier-returns.index') }}" class="nav-link {{ request()->routeIs('supplier-returns.*') ? 'active' : '' }}">
            <i class="bi bi-arrow-return-left"></i>
            <span>Retur Barang Supplier</span>
        </a>

        <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
            <i class="bi bi-wrench"></i>
            <span>Daftar Jasa / Tarif</span>
        </a>

        <!-- KEUANGAN & TRANSAKSI (Owner & Admin) -->
        <div class="sidebar-heading">Kasir & Faktur</div>

        <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
            <i class="bi bi-cash-stack"></i>
            <span>Pembayaran / POS</span>
        </a>

        <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i>
            <span>Invoice Faktur</span>
        </a>
    @endif

    <!-- LAPORAN & PENGATURAN (Khusus Owner) -->
    @if($role === 'owner')
        <div class="sidebar-heading">Laporan & Sistem</div>

        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Laporan & Analitik</span>
        </a>

        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i>
            <span>Pengaturan Bengkel</span>
        </a>
    @endif

    <!-- INFO AKUN / FOOTER SIDEBAR -->
    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 px-2">
        <div class="d-flex align-items-center gap-2 text-muted small">
            <span class="badge bg-secondary">{{ strtoupper($role) }}</span>
            <span class="text-truncate">{{ $user?->name ?? 'User' }}</span>
        </div>
    </div>
</div>
