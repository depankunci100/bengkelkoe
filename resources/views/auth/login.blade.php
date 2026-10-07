@extends('layouts.auth')

@section('title', 'Login Masuk')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5 col-xl-4">
        
        <div class="text-center mb-4 text-white">
            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow" style="width: 56px; height: 56px;">
                <i class="bi bi-wrench-adjustable-circle-fill fs-2"></i>
            </div>
            <h3 class="fw-bold mb-1">{{ \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL') }}</h3>
            <p class="text-white-50 small mb-0">{{ \App\Models\WorkshopSetting::get('workshop_tagline', 'Sistem Informasi Manajemen Bengkel') }}</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-lg border-0 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold text-dark mb-3">Masuk ke Akun</h5>
                
                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="email" class="form-control border-start-0 @error('email') is-invalid @enderror" value="{{ old('email', 'owner@bengkel.com') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold">Kata Sandi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control border-start-0 @error('password') is-invalid @enderror" value="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                        <label class="form-check-label small text-muted" for="remember">Ingat Saya</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk Sistem
                    </button>
                </form>

                <hr class="my-4 text-muted">

                <div>
                    <small class="text-muted fw-bold d-block mb-2 text-center">⚡ 1-KLIK DEMO LOGIN CEPAT</small>
                    <div class="d-grid gap-2">
                        <a href="{{ route('login.quick', 'owner') }}" class="btn btn-outline-primary btn-sm text-start d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-person-badge-fill me-1"></i> <strong>Owner</strong> (Bambang Wijaya)</span>
                            <span class="badge bg-primary">Full Akses</span>
                        </a>
                        <a href="{{ route('login.quick', 'admin') }}" class="btn btn-outline-success btn-sm text-start d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-cash-coin me-1"></i> <strong>Admin / Kasir</strong> (Siti Rahma)</span>
                            <span class="badge bg-success">Kasir & WO</span>
                        </a>
                        <a href="{{ route('login.quick', 'technician') }}" class="btn btn-outline-warning btn-sm text-start d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-wrench me-1"></i> <strong>Teknisi</strong> (Agus Pratama)</span>
                            <span class="badge bg-warning text-dark">Inspeksi & WO</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-center text-white-50 small">
            &copy; 2026 {{ \App\Models\WorkshopSetting::get('workshop_name', 'SIM BENGKEL') }}. Laravel 13 + Bootstrap 5.
        </div>

    </div>
</div>
@endsection
