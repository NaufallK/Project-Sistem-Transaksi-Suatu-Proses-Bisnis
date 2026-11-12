<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\KartuStokController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PengadaanController;
use App\Http\Controllers\PenjualanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('barang.index');
});

Route::resource('barang', BarangController::class)
    ->only(['index', 'create', 'store', 'destroy']);

Route::patch('barang/{barang}/activate', [BarangController::class, 'activate'])
    ->name('barang.activate');

Route::resource('pengadaan', PengadaanController::class)
    ->only(['index', 'create', 'store', 'destroy']);

Route::resource('penerimaan', PenerimaanController::class)
    ->only(['index', 'create', 'store', 'destroy']);

Route::resource('penjualan', PenjualanController::class)
    ->only(['index', 'create', 'store', 'destroy']);

Route::get('kartu-stok', [KartuStokController::class, 'index'])
    ->name('kartu-stok.index');

Route::get('harga-jual', [PenjualanController::class, 'hargaJual'])
    ->name('harga-jual');
