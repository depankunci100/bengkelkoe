<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PartCategoryController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierReturnController;
use App\Http\Controllers\SupplierSalesController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (or login)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ==========================================
// 1. PUBLIC ROUTES (Tanpa Login)
// ==========================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/login/quick/{role}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Customer Approval Web (Section 37 Master Prompt)
Route::get('/approval/{token}', [ApprovalController::class, 'publicView'])->name('customer.approval');
Route::post('/approval/{token}', [ApprovalController::class, 'submitCustomerDecision'])->name('customer.approval.submit');

// ==========================================
// 2. AUTHENTICATED WEB APPLICATION ROUTES
// ==========================================
Route::middleware(['auth'])->group(function () {

    // Dashboard (Sections 7 & 8)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Customer Management (Sections 9 & 10)
    Route::resource('customers', CustomerController::class);

    // Vehicles
    Route::resource('vehicles', VehicleController::class)->only(['index', 'store', 'update', 'destroy']);

    // Work Orders (Sections 11, 12, 17, 18, 19)
    Route::resource('work-orders', WorkOrderController::class);
    Route::post('/work-orders/{work_order}/items', [WorkOrderController::class, 'addItem'])->name('work-orders.add-item');
    Route::post('/work-orders/{work_order}/additional-items', [WorkOrderController::class, 'addAdditionalItem'])->name('work-orders.add-additional');
    Route::delete('/work-orders/{work_order}/items/{item}', [WorkOrderController::class, 'removeItem'])->name('work-orders.remove-item');
    Route::post('/work-orders/{work_order}/status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
    Route::post('/work-orders/{work_order}/assign-tech', [WorkOrderController::class, 'assignTechnician'])->name('work-orders.assign-tech');
    Route::post('/work-orders/{work_order}/assign-sales', [WorkOrderController::class, 'assignSales'])->name('work-orders.assign-sales');
    Route::post('/work-orders/{work_order}/send-whatsapp', [WorkOrderController::class, 'sendWhatsappApproval'])->name('work-orders.send-whatsapp');

    // Inspections (Section 14)
    Route::get('/inspections', [InspectionController::class, 'index'])->name('inspections.index');
    Route::get('/inspections/{workOrderId}', [InspectionController::class, 'show'])->name('inspections.show');
    Route::put('/inspections/items/{item}', [InspectionController::class, 'updateItem'])->name('inspections.update-item');
    Route::post('/inspections/{inspection}/items', [InspectionController::class, 'addItem'])->name('inspections.add-item');
    Route::post('/inspections/{inspection}/complete', [InspectionController::class, 'complete'])->name('inspections.complete');

    // Approval Dashboard Internal (Sections 15 & 16)
    Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::get('/approvals/{id}', [ApprovalController::class, 'show'])->name('approvals.show');

    // Inventory & Spareparts (Sections 20 & 21)
    Route::resource('parts', PartController::class);
    Route::post('/parts/{part}/adjust-stock', [PartController::class, 'adjustStock'])->name('parts.adjust-stock');
    Route::resource('part-categories', PartCategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('suppliers', SupplierController::class);
    Route::post('/suppliers/{supplier}/sales', [SupplierSalesController::class, 'store'])->name('suppliers.sales.store');
    Route::put('/suppliers/{supplier}/sales/{sale}', [SupplierSalesController::class, 'update'])->name('suppliers.sales.update');
    Route::delete('/suppliers/{supplier}/sales/{sale}', [SupplierSalesController::class, 'destroy'])->name('suppliers.sales.destroy');
    Route::get('/api/suppliers/{supplier}/sales', [SupplierSalesController::class, 'getSalesBySupplier'])->name('suppliers.sales.api');

    // Supplier Returns (Faktur Pengembalian / Retur Barang Rusak ke Supplier)
    Route::resource('supplier-returns', SupplierReturnController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/supplier-returns/{supplier_return}/status', [SupplierReturnController::class, 'updateStatus'])->name('supplier-returns.status');
    Route::get('/supplier-returns/{supplier_return}/print', [SupplierReturnController::class, 'print'])->name('supplier-returns.print');

    // Services (Daftar Jasa & Tarif)
    Route::resource('services', ServiceController::class)->only(['index', 'store', 'update', 'destroy']);

    // Barcode Scanner & Dispatch Verification Hub (Stock Opname & Barang Keluar)
    Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner.index');
    Route::get('/scanner/lookup', [ScannerController::class, 'lookup'])->name('scanner.lookup');
    Route::post('/scanner/opname', [ScannerController::class, 'storeOpname'])->name('scanner.opname');
    Route::post('/scanner/stock-in', [ScannerController::class, 'storeStockIn'])->name('scanner.stock-in');
    Route::post('/scanner/stock-out', [ScannerController::class, 'storeStockOut'])->name('scanner.stock-out');
    Route::get('/scanner/work-order', [ScannerController::class, 'lookupWorkOrder'])->name('scanner.work-order');
    Route::post('/scanner/verify-item', [ScannerController::class, 'verifyWorkOrderItem'])->name('scanner.verify-item');
    Route::post('/scanner/work-orders/{work_order}/complete-verification', [ScannerController::class, 'completeWorkOrderVerification'])->name('scanner.complete-verification');
    Route::get('/work-orders/{work_order}/picking-slip', [ScannerController::class, 'printPickingSlip'])->name('work-orders.picking-slip');
    Route::get('/parts/{part}/barcode-print', [ScannerController::class, 'printBarcode'])->name('parts.barcode-print');

    // Payments & Kasir POS (Section 22)
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

    // Invoices & Print (Section 23)
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::put('/invoices/{invoice}/discount', [InvoiceController::class, 'updateDiscount'])->name('invoices.update-discount');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

    // Reports (Section 24)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // Workshop Settings (Section 30)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});
