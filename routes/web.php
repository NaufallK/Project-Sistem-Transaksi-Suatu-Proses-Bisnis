<?php

use App\Http\Controllers\BarangController;
use App\Http\Controllers\KartuStokController;
use App\Http\Controllers\MarginPenjualanController;
use App\Http\Controllers\PenerimaanController;
use App\Http\Controllers\PengadaanController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('barang.index');
});

// Master: Barang (VIEW view_barang_satuan + Stored Procedure sp_tambah_barang)
Route::resource('barang', BarangController::class)
    ->except(['show'])
    ->parameters(['barang' => 'id']);
Route::patch('barang/{id}/activate', [BarangController::class, 'activate'])
    ->name('barang.activate');

// Master: Satuan
Route::resource('satuan', SatuanController::class)
    ->except(['show'])
    ->parameters(['satuan' => 'id']);
Route::patch('satuan/{id}/activate', [SatuanController::class, 'activate'])
    ->name('satuan.activate');

// Master: Vendor
Route::resource('vendor', VendorController::class)
    ->except(['show'])
    ->parameters(['vendor' => 'id']);
Route::patch('vendor/{id}/activate', [VendorController::class, 'activate'])
    ->name('vendor.activate');

// Master: Margin Penjualan
Route::resource('margin', MarginPenjualanController::class)
    ->except(['show'])
    ->parameters(['margin' => 'id']);
Route::patch('margin/{id}/activate', [MarginPenjualanController::class, 'activate'])
    ->name('margin.activate');

// Transaksi: Pengadaan
Route::resource('pengadaan', PengadaanController::class)
    ->only(['index', 'create', 'store', 'destroy']);

// Transaksi: Penerimaan (TRIGGER trg_kartu_stok aktif saat INSERT detail_penerimaan)
Route::resource('penerimaan', PenerimaanController::class)
    ->only(['index', 'create', 'store', 'destroy']);

// Transaksi: Penjualan (FUNCTION fn_hitung_harga_jual)
Route::resource('penjualan', PenjualanController::class)
    ->only(['index', 'create', 'store', 'destroy']);

// Kartu Stok
Route::get('kartu-stok', [KartuStokController::class, 'index'])
    ->name('kartu-stok.index');

// Preview Harga Jual via Function Database
Route::get('harga-jual', [PenjualanController::class, 'hargaJual'])
    ->name('harga-jual');
