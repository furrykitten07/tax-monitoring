<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceExportController;
use App\Http\Controllers\TaxRecordExportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoices/{invoice}/export', [InvoiceExportController::class, 'export'])->name('invoices.export');
Route::get('/tax-records/export', [TaxRecordExportController::class, 'export'])->name('tax-records.export');
