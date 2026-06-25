<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LockController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaiterController;
use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

Route::get('/manifest.json', [AppController::class, 'manifest'])->name('manifest');

Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

Route::get('/offline', function () {
    return view('offline');
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/pin-login', [LoginController::class, 'showPinForm'])->name('pin.login');
    Route::post('/pin-login', [LoginController::class, 'pinLogin'])->name('pin.login.verify')->middleware('throttle:5,1');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token, 'email' => request('email')]);
    })->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile.show');
    Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/branch/switch/{branch}', [BranchController::class, 'switchBranch'])->name('branch.switch');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'chartData'])->name('dashboard.chart-data');

    Route::prefix('pos')->name('pos.')->middleware('role:Cashier,Super Admin,Manager')->group(function () {
        Route::get('/', [POSController::class, 'index'])->name('index');
        Route::get('/orders', [POSController::class, 'pendingOrders'])->name('orders');
        Route::get('/pending-count', [POSController::class, 'pendingCount'])->name('pending-count');
        Route::post('/hold', [POSController::class, 'hold'])->name('hold');
        Route::post('/payment', [POSController::class, 'payment'])->name('payment');
        Route::post('/orders/{order}/accept', [POSController::class, 'acceptOrder'])->name('accept-order');
        Route::post('/order-items/unavailable', [POSController::class, 'markItemUnavailable'])->name('item-unavailable');
        Route::get('/resume/{bill}', [POSController::class, 'resumeHold'])->name('resume');
    });

    Route::prefix('products')->name('products.')->middleware('role:Super Admin,Manager,Store Keeper')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('categories')->name('categories.')->middleware('role:Super Admin,Manager,Store Keeper')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('units')->name('units.')->middleware('role:Super Admin,Manager,Store Keeper')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('index');
        Route::get('/create', [UnitController::class, 'create'])->name('create');
        Route::post('/', [UnitController::class, 'store'])->name('store');
        Route::get('/{unit}/edit', [UnitController::class, 'edit'])->name('edit');
        Route::put('/{unit}', [UnitController::class, 'update'])->name('update');
        Route::delete('/{unit}', [UnitController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('batches')->name('batches.')->middleware('role:Super Admin,Manager,Store Keeper')->group(function () {
        Route::get('/', [BatchController::class, 'index'])->name('index');
        Route::get('/create', [BatchController::class, 'create'])->name('create');
        Route::post('/', [BatchController::class, 'store'])->name('store');
        Route::get('/{batch}/edit', [BatchController::class, 'edit'])->name('edit');
        Route::put('/{batch}', [BatchController::class, 'update'])->name('update');
        Route::delete('/{batch}', [BatchController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('customers')->name('customers.')->middleware('role:Super Admin,Manager,Cashier,Store Keeper,Accountant')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::get('/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('suppliers')->name('suppliers.')->middleware('role:Super Admin,Manager,Cashier,Store Keeper,Accountant')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('orders')->name('orders.')->middleware('role:Super Admin,Manager,Cashier')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::put('/{order}', [OrderController::class, 'update'])->name('update');
        Route::put('/{order}/status', [OrderController::class, 'updateStatus'])->name('status');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('billing')->name('billing.')->middleware('role:Super Admin,Manager,Cashier,Accountant')->group(function () {
        Route::get('/', [BillController::class, 'index'])->name('index');
        Route::get('/{bill}', [BillController::class, 'show'])->name('show');
        Route::get('/{bill}/print', [BillController::class, 'print'])->name('print');
        Route::get('/{bill}/pdf', [BillController::class, 'exportPdf'])->name('pdf');
    });

    Route::get('/billing/{bill}/receipt-content', [BillController::class, 'receiptContent'])
        ->name('billing.receipt-content')
        ->middleware('role:Super Admin,Manager,Cashier,Accountant,Waiter');

    Route::get('/apk/download', [AppController::class, 'downloadApk'])->name('apk.download');
    Route::get('/desktop-shortcut', [AppController::class, 'desktopShortcut'])->name('desktop.shortcut');

    Route::prefix('expenses')->name('expenses.')->middleware('role:Super Admin,Manager,Cashier,Accountant')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/create', [ExpenseController::class, 'create'])->name('create');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tables')->name('tables.')->middleware('role:Super Admin,Manager')->group(function () {
        Route::get('/', [TableController::class, 'index'])->name('index');
        Route::get('/create', [TableController::class, 'create'])->name('create');
        Route::post('/', [TableController::class, 'store'])->name('store');
        Route::get('/{table}/edit', [TableController::class, 'edit'])->name('edit');
        Route::put('/{table}', [TableController::class, 'update'])->name('update');
        Route::delete('/{table}', [TableController::class, 'destroy'])->name('destroy');
        Route::get('/qr/{id}', [TableController::class, 'showQr'])->name('qr');
    });

    Route::prefix('purchases')->name('purchases.')->middleware('role:Super Admin,Manager,Store Keeper')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::get('/{purchase}/edit', [PurchaseController::class, 'edit'])->name('edit');
        Route::put('/{purchase}', [PurchaseController::class, 'update'])->name('update');
        Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('kitchen')->name('kitchen.')->middleware('role:Kitchen Staff,Super Admin,Manager')->group(function () {
        Route::get('/', [KitchenController::class, 'index'])->name('index');
        Route::get('/orders', [KitchenController::class, 'getOrders'])->name('orders');
        Route::put('/orders/{order}/status', [KitchenController::class, 'updateStatus'])->name('update-status');
    });

    Route::prefix('waiter')->name('waiter.')->middleware('role:Waiter,Super Admin,Manager')->group(function () {
        Route::get('/', [WaiterController::class, 'index'])->name('index');
        Route::get('/orders', [WaiterController::class, 'orders'])->name('orders');
        Route::post('/orders', [WaiterController::class, 'storeOrder'])->name('orders.store');
        Route::get('/orders/create', [WaiterController::class, 'createOrder'])->name('orders.create');
        Route::get('/tables', [WaiterController::class, 'tables'])->name('tables');
        Route::get('/tables-data', [WaiterController::class, 'tablesData'])->name('tables.data');
        Route::get('/products-data', [WaiterController::class, 'productsData'])->name('products');
        Route::get('/orders-data', [WaiterController::class, 'ordersData'])->name('orders.data');
        Route::get('/orders/{order}/detail', [WaiterController::class, 'orderDetail'])->name('orders.detail');
        Route::post('/orders/serve', [WaiterController::class, 'markServed'])->name('orders.serve');
        Route::post('/orders/cancel', [WaiterController::class, 'cancelOrder'])->name('orders.cancel');
        Route::post('/orders/request-bill', [WaiterController::class, 'requestBill'])->name('orders.request-bill');
        Route::post('/orders/pay', [WaiterController::class, 'processPayment'])->name('orders.pay');
    });

    Route::prefix('reports')->name('reports.')->middleware('role:Super Admin,Manager,Accountant,Store Keeper')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
    });

    Route::prefix('settings')->name('settings.')->middleware('role:Super Admin,Manager')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/', [SettingController::class, 'update'])->name('update');
        Route::post('/backup', [SettingController::class, 'backupDatabase'])->name('backup');
    });

    Route::prefix('activity-logs')->name('activities.')->middleware('role:Super Admin,Manager')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    });

    Route::prefix('users')->name('users.')->middleware('role:Super Admin,Manager')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('branches')->name('branches.')->middleware('role:Super Admin')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::get('/create', [BranchController::class, 'create'])->name('create');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
    });

    Route::post('/lock', [LockController::class, 'lock'])->name('lock');
    Route::get('/lock', [LockController::class, 'showLock'])->name('lock.screen');
    Route::post('/unlock', [LockController::class, 'unlock'])->name('unlock');
});
