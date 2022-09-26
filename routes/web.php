<?php

use App\Imports\InvoiceImport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/invoice/export', [\App\Http\Controllers\InvoiceController::class, 'export'])->name('invoices.export');
Route::get('/invoice/import', [\App\Http\Controllers\InvoiceController::class, 'import'])->name('invoices.import');
Route::post('/invoice/import', [\App\Http\Controllers\InvoiceController::class, 'importStore'])->name('invoices.importStore');
Route::get('prueba', function (){
    return Excel::toCollection(new InvoiceImport, 'csv/invoices.csv');
});
