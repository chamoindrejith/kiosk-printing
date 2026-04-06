<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/kiosk/demo');
});

Route::prefix('kiosk/{printerCode}')->group(function () {
    Route::get('/', [App\Http\Controllers\Kiosk\KioskController::class, 'landing'])->name('kiosk.landing');
    Route::post('/upload', [App\Http\Controllers\Kiosk\KioskController::class, 'upload'])->name('kiosk.upload');
    Route::get('/options', [App\Http\Controllers\Kiosk\KioskController::class, 'options'])->name('kiosk.options');
    Route::post('/configure', [App\Http\Controllers\Kiosk\KioskController::class, 'configure'])->name('kiosk.configure');
    Route::get('/price', [App\Http\Controllers\Kiosk\KioskController::class, 'price'])->name('kiosk.price');
    Route::post('/initiate-payment', [App\Http\Controllers\Kiosk\KioskController::class, 'initiatePayment'])->name('kiosk.payment.initiate');
    Route::get('/payment', [App\Http\Controllers\Kiosk\KioskController::class, 'payment'])->name('kiosk.payment.show');
    Route::post('/payment/check', [App\Http\Controllers\Kiosk\KioskController::class, 'checkPayment'])->name('kiosk.payment.check');
    Route::get('/confirm', [App\Http\Controllers\Kiosk\KioskController::class, 'confirmForm'])->name('kiosk.confirm.form');
    Route::post('/confirm-print', [App\Http\Controllers\Kiosk\KioskController::class, 'confirmPrint'])->name('kiosk.confirm');
    Route::get('/status/{jobId}', [App\Http\Controllers\Kiosk\KioskController::class, 'status'])->name('kiosk.status');
    Route::post('/cancel-job', [App\Http\Controllers\Kiosk\KioskController::class, 'cancelJob'])->name('kiosk.cancel');
    Route::delete('/remove', [App\Http\Controllers\Kiosk\KioskController::class, 'removeUpload'])->name('kiosk.remove');
    Route::get('/mock-pay/{paymentId}', [App\Http\Controllers\Kiosk\KioskController::class, 'mockPay'])->name('kiosk.mock-pay');
});

Route::prefix('admin')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    Route::resource('printers', App\Http\Controllers\Admin\PrinterController::class)->names('admin.printers')->except(['show']);
    Route::resource('pricing', App\Http\Controllers\Admin\PricingController::class)->names('admin.pricing')->except(['show']);
    Route::resource('jobs', App\Http\Controllers\Admin\JobController::class)->only(['index', 'show', 'update'])->names('admin.jobs');
    Route::post('/jobs/{job}/retry', [App\Http\Controllers\Admin\JobController::class, 'retry'])->name('admin.jobs.retry');
    Route::post('/jobs/{job}/cancel', [App\Http\Controllers\Admin\JobController::class, 'cancel'])->name('admin.jobs.cancel');
    
    Route::get('/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('/payments/{payment}', [App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('admin.payments.show');
    
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports');
});

Route::post('/webhook/payment', [App\Http\Controllers\Webhook\PaymentWebhookController::class, 'handle'])->name('webhook.payment');