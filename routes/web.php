<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceExportController;
use App\Http\Controllers\TaxRecordExportController;
use App\Http\Controllers\TaxRecordFilteredExportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoices/{invoice}/export', [InvoiceExportController::class, 'export'])->name('invoices.export');
Route::get('/tax-records/{taxRecord}/export', [TaxRecordExportController::class, 'export'])->name('tax-records.export');
Route::get('/tax-records/export-filtered', [TaxRecordFilteredExportController::class, 'exportFiltered'])->name('tax-records.export-filtered');
