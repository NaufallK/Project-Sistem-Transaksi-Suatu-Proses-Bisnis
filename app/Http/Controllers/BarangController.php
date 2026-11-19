<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index()
    {
        // Menggunakan VIEW database agar JOIN barang + satuan tidak ditulis ulang di PHP.
        $barangs = DB::select('SELECT * FROM view_barang_satuan ORDER BY idbarang ASC');

        return view('barang.index', compact('barangs'));
    }

    public function create()
    {
        $satuans = DB::select(
            'SELECT idsatuan, nama_satuan FROM satuan WHERE status = 1 ORDER BY nama_satuan ASC'
        );

        return view('barang.create', compact('satuans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:100'],
            'idsatuan' => ['required', 'integer'],
            'harga' => ['required', 'numeric', 'min:0'],
        ]);

        // Semua logika INSERT ditangani oleh STORED PROCEDURE di MySQL.
        DB::statement('CALL sp_tambah_barang(?, ?, ?, ?)', [
            $validated['nama'],
            $validated['jenis'],
            (int) $validated['idsatuan'],
            (float) $validated['harga'],
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan lewat sp_tambah_barang.');
    }

    public function edit(int $id)
    {
        $barang = DB::selectOne('SELECT * FROM barang WHERE idbarang = ?', [$id]);

        if (! $barang) {
            return redirect()->route('barang.index')
                ->withErrors(['barang' => 'Barang tidak ditemukan.']);
        }

        $satuans = DB::select(
            'SELECT idsatuan, nama_satuan FROM satuan WHERE status = 1 ORDER BY nama_satuan ASC'
        );

        return view('barang.edit', compact('barang', 'satuans'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'jenis' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:100'],
            'idsatuan' => ['required', 'integer'],
            'harga' => ['required', 'numeric', 'min:0'],
        ]);

        DB::statement(
            'UPDATE barang SET jenis = ?, nama = ?, idsatuan = ?, harga = ? WHERE idbarang = ?',
            [
                $validated['jenis'],
                $validated['nama'],
                (int) $validated['idsatuan'],
                (float) $validated['harga'],
                $id,
            ]
        );

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil diubah.');
    }

    public function destroy(int $id)
    {
        DB::statement('UPDATE barang SET status = 0 WHERE idbarang = ?', [$id]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil dinonaktifkan.');
    }

    public function activate(int $id)
    {
        DB::statement('UPDATE barang SET status = 1 WHERE idbarang = ?', [$id]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil diaktifkan.');
    }
}
