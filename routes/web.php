<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\VehicleController as CustomerVehicleController;
use App\Http\Controllers\Portal\PublicInspectionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\CustomerController as StaffCustomerController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\FinanceController;
use App\Http\Controllers\Staff\InspectionController;
use App\Http\Controllers\Staff\InventoryItemController;
use App\Http\Controllers\Staff\InventoryMovementController;
use App\Http\Controllers\Staff\InventoryReportController;
use App\Http\Controllers\Staff\InvoiceController;
use App\Http\Controllers\Staff\QuotationController;
use App\Http\Controllers\Staff\VehicleController as StaffVehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Secure, token-based customer access links — no login required.
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/i/{token}', [PublicInspectionController::class, 'show'])->name('inspection');
    Route::post('/i/{token}/quotations/{quotation}/decision', [PublicInspectionController::class, 'decide'])->name('quotation.decide');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasAnyRole(['manager', 'service_advisor', 'technician', 'inventory_manager'])) {
        return redirect()->route('staff.dashboard');
    }

    return redirect()->route('customer.dashboard');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Workshop staff area.
Route::middleware(['auth', 'role:admin|manager|service_advisor|technician|inventory_manager', 'branch.access'])
    ->prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffDashboardController::class, 'index'])->name('dashboard');

        Route::get('/vehicles', [StaffVehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehicles/create', [StaffVehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehicles', [StaffVehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}', [StaffVehicleController::class, 'show'])->name('vehicles.show');
        Route::get('/vehicles/{vehicle}/edit', [StaffVehicleController::class, 'edit'])->name('vehicles.edit');
        Route::patch('/vehicles/{vehicle}', [StaffVehicleController::class, 'update'])->name('vehicles.update');
        Route::get('/vehicles/{vehicle}/qr', [StaffVehicleController::class, 'qr'])->name('vehicles.qr');

        Route::get('/vehicles/{vehicle}/inspections/create', [InspectionController::class, 'create'])->name('inspections.create');
        Route::post('/vehicles/{vehicle}/inspections', [InspectionController::class, 'store'])->name('inspections.store');
        Route::get('/inspections/{inspection}', [InspectionController::class, 'show'])->name('inspections.show');
        Route::patch('/inspections/{inspection}/start', [InspectionController::class, 'startInspection'])->name('inspections.start');
        Route::post('/inspections/{inspection}/items', [InspectionController::class, 'storeItem'])->name('inspections.items.store');
        Route::post('/inspections/{inspection}/items/batch-update', [InspectionController::class, 'batchUpdateItems'])->name('inspections.items.batch-update');
        Route::post('/inspections/{inspection}/items/{item}', [InspectionController::class, 'updateItem'])->name('inspections.items.update');
        Route::patch('/inspections/{inspection}/complete-diagnosis', [InspectionController::class, 'completeDiagnosis'])->name('inspections.complete-diagnosis');
        Route::patch('/inspections/{inspection}/ready-no-repair', [InspectionController::class, 'markReadyNoRepair'])->name('inspections.ready-no-repair');
        Route::patch('/inspections/{inspection}/ready', [InspectionController::class, 'markReady'])->name('inspections.ready');
        Route::patch('/inspections/{inspection}/collected', [InspectionController::class, 'markCollected'])->name('inspections.collected');

        Route::post('/inspections/{inspection}/diagram-marks', [InspectionController::class, 'storeDiagramMark'])->name('inspections.diagram.store');
        Route::delete('/inspections/{inspection}/diagram-marks/{mark}', [InspectionController::class, 'destroyDiagramMark'])->name('inspections.diagram.destroy');

        Route::post('/inspections/{inspection}/comments', [InspectionController::class, 'storeComment'])->name('inspections.comments.store');

        // Quotation & billing live on their own pages — reached from the Customers/Invoices
        // hub, never from the inspection sheet, which is purely the technician's diagnosis.
        Route::get('/inspections/{inspection}/quotation', [QuotationController::class, 'show'])->name('quotations.show');
        Route::patch('/inspections/{inspection}/quotation-items/{item}/status', [QuotationController::class, 'updateItemStatus'])->name('quotations.items.status');

        // Pricing the job is a manager/service-advisor/admin decision — technicians diagnose, they don't quote.
        Route::middleware('role:admin|manager|service_advisor')->group(function () {
            Route::get('/inspections/{inspection}/quotation/create', [QuotationController::class, 'create'])->name('quotations.create');
            Route::post('/inspections/{inspection}/quotation', [QuotationController::class, 'store'])->name('quotations.store');
            Route::post('/inspections/{inspection}/quotation/{quotation}/decision', [QuotationController::class, 'recordDecision'])->name('quotations.decision');
        });

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/create', [InvoiceController::class, 'selectCustomer'])->name('invoices.select');
        Route::get('/inspections/{inspection}/invoice/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/inspections/{inspection}/invoice', [InvoiceController::class, 'store'])->name('invoices.store');

        // Walk-in / counter-sale billing — a vehicle-only invoice with no inspection or
        // quotation behind it, for customers who just need something billed on the spot.
        Route::middleware('role:admin|manager|service_advisor')->group(function () {
            Route::get('/vehicles/{vehicle}/quick-invoice', [InvoiceController::class, 'createQuick'])->name('invoices.quick-create');
            Route::post('/vehicles/{vehicle}/quick-invoice', [InvoiceController::class, 'storeQuick'])->name('invoices.quick-store');
        });

        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments.store');

        Route::get('/customers', [StaffCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/create', [StaffCustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [StaffCustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/{customer}', [StaffCustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [StaffCustomerController::class, 'edit'])->name('customers.edit');
        Route::patch('/customers/{customer}', [StaffCustomerController::class, 'update'])->name('customers.update');
        Route::patch('/customers/{customer}/toggle', [StaffCustomerController::class, 'toggle'])->name('customers.toggle');

        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');

        // Inventory — everyone on staff can see stock; only admin/manager/inventory_manager can touch it.
        // Static segments (create/movements/reports) are registered before the {stockItem} wildcard
        // so "create" etc. never gets swallowed as an item id.
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryItemController::class, 'index'])->name('items.index');
            Route::get('/movements', [InventoryMovementController::class, 'index'])->name('movements.index');
            Route::get('/reports', [InventoryReportController::class, 'index'])->name('reports.index');

            Route::middleware('role:admin|manager|inventory_manager')->group(function () {
                Route::get('/items/create', [InventoryItemController::class, 'create'])->name('items.create');
                Route::post('/items', [InventoryItemController::class, 'store'])->name('items.store');
            });

            Route::get('/items/{stockItem}', [InventoryItemController::class, 'show'])->name('items.show');

            Route::middleware('role:admin|manager|inventory_manager')->group(function () {
                Route::get('/items/{stockItem}/edit', [InventoryItemController::class, 'edit'])->name('items.edit');
                Route::patch('/items/{stockItem}', [InventoryItemController::class, 'update'])->name('items.update');
                Route::patch('/items/{stockItem}/toggle', [InventoryItemController::class, 'toggle'])->name('items.toggle');

                Route::get('/items/{stockItem}/receive', [InventoryMovementController::class, 'receive'])->name('items.receive');
                Route::post('/items/{stockItem}/receive', [InventoryMovementController::class, 'storeReceive'])->name('items.receive.store');
                Route::get('/items/{stockItem}/issue', [InventoryMovementController::class, 'issue'])->name('items.issue');
                Route::post('/items/{stockItem}/issue', [InventoryMovementController::class, 'storeIssue'])->name('items.issue.store');
                Route::get('/items/{stockItem}/adjust', [InventoryMovementController::class, 'adjust'])->name('items.adjust');
                Route::post('/items/{stockItem}/adjust', [InventoryMovementController::class, 'storeAdjust'])->name('items.adjust.store');
            });
        });
    });

// Admin-only staff/branch/system management.
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('branches', BranchController::class)->except('show', 'destroy');
    Route::resource('staff', StaffController::class)->except('show');
    Route::get('/activity', [SystemController::class, 'activity'])->name('activity');
    Route::post('/service-reminders/run', [SystemController::class, 'runServiceReminders'])->name('service-reminders.run');
});

// Customer portal (requires login — full service history).
Route::middleware(['auth', 'role:customer'])->prefix('my')->name('customer.')->group(function () {
    Route::get('/', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/vehicles/{vehicle}', [CustomerVehicleController::class, 'show'])->name('vehicles.show');
    Route::get('/invoices/{invoice}/pdf', [\App\Http\Controllers\Customer\InvoiceController::class, 'pdf'])->name('invoices.pdf');
});

require __DIR__.'/auth.php';
