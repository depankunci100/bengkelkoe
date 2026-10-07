@extends('layouts.app')

@section('title', 'Buat Work Order Baru')

@section('header')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('work-orders.index') }}" class="btn btn-sm btn-light border">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 class="fw-bold mb-0 text-dark">Buat Work Order (SPK Servis) Baru</h4>
        <small class="text-muted">Daftarkan keluhan kendaraan dan tugaskan teknisi pemeriksa.</small>
    </div>
</div>
@endsection

@section('content')
<div class="row justify-content-center" x-data="woForm()">
    <div class="col-12 col-lg-8">
        <form action="{{ route('work-orders.store') }}" method="POST">
            @csrf

            <x-card title="Data Pendaftaran Servis" icon="clipboard-plus">

                <!-- 1. Pilih Pelanggan -->
                <div class="mb-3">
                    <label for="customer_id" class="form-label fw-semibold">Pilih Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" x-model="selectedCustomer" @change="updateVehicles()" required>
                        <option value="">-- Pilih Customer Terdaftar --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ (string) old('customer_id', $selectedCustomerId) === (string) $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone }})
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text text-muted">
                        Belum terdaftar? <a href="{{ route('customers.create') }}" target="_blank">Tambah customer baru</a>
                    </div>
                </div>

                <!-- 2. Pilih Kendaraan (Dinamis sesuai Customer) -->
                <div class="mb-3">
                    <label for="vehicle_id" class="form-label fw-semibold">Pilih Kendaraan <span class="text-danger">*</span></label>
                    <select name="vehicle_id" id="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" x-model="selectedVehicle" required>
                        <option value="">-- Pilih Kendaraan Pelanggan --</option>
                        <template x-for="v in availableVehicles" :key="v.id">
                            <option :value="v.id" x-text="v.plate_number + ' - ' + v.brand + ' ' + v.model + ' (' + v.year + ')'"></option>
                        </template>
                    </select>
                    @error('vehicle_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row">
                    <!-- Odometer Masuk -->
                    <div class="col-md-6">
                        <x-form.input name="odometer_in" type="number" label="Odometer Masuk (KM)" placeholder="Contoh: 45000" icon="speedometer2" />
                    </div>

                    <!-- Tugaskan Teknisi -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="technician_id" class="form-label fw-semibold">Tugaskan Teknisi (Mekanik)</label>
                            <select name="technician_id" id="technician_id" class="form-select">
                                <option value="">-- Tugaskan Nanti (Status: Draft) --</option>
                                @foreach($technicians as $t)
                                    <option value="{{ $t->id }}">
                                        {{ $t->name }} (Spesialis: {{ $t->specialization ?? 'Mekanik Umum' }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted">Jika dipilih, status otomatis menjadi <strong>INSPECTION</strong>.</div>
                        </div>
                    </div>
                </div>

                <!-- Keluhan Pelanggan -->
                <x-form.textarea name="complaint" label="Keluhan Utama / Permintaan Servis" placeholder="Tuliskan keluhan yang dirasakan pelanggan, gejala suara, getaran, atau paket servis berkala yang diminta..." rows="4" required help="Keluhan ini akan menjadi panduan teknisi dalam melakukan inspeksi fisik." />

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('work-orders.index') }}" class="btn btn-light border px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-4 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> Buat & Buka Work Order
                    </button>
                </div>

            </x-card>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function woForm() {
    const allCustomers = @json($customers);
    const initialCustomerId = "{{ old('customer_id', $selectedCustomerId ?? '') }}";
    const initialVehicleId = "{{ old('vehicle_id', $selectedVehicleId ?? '') }}";

    return {
        selectedCustomer: initialCustomerId,
        selectedVehicle: initialVehicleId,
        availableVehicles: [],

        init() {
            if (this.selectedCustomer) {
                this.updateVehicles();
                if (initialVehicleId) {
                    this.selectedVehicle = initialVehicleId;
                }
            }
        },

        updateVehicles() {
            const customer = allCustomers.find(c => c.id == this.selectedCustomer);
            this.availableVehicles = customer ? customer.vehicles : [];
            if (!this.availableVehicles.find(v => v.id == this.selectedVehicle)) {
                this.selectedVehicle = this.availableVehicles.length > 0 ? this.availableVehicles[0].id : '';
            }
        }
    }
}
</script>
@endpush
