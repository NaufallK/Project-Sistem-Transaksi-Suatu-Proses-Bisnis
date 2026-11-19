<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = DB::select('SELECT idvendor, nama_vendor, badan_hukum, status FROM vendor ORDER BY idvendor ASC');

        return view('vendor.index', compact('vendors'));
    }

    public function create()
    {
        return view('vendor.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_vendor' => ['required', 'string', 'max:100'],
            'badan_hukum' => ['required', 'string', 'max:100'],
        ]);

        DB::statement('INSERT INTO vendor (nama_vendor, status, badan_hukum) VALUES (?, 1, ?)', [
            $validated['nama_vendor'],
            $validated['badan_hukum'],
        ]);

        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $vendor = DB::selectOne('SELECT idvendor, nama_vendor, badan_hukum, status FROM vendor WHERE idvendor = ?', [$id]);

        if (! $vendor) {
            return redirect()->route('vendor.index')->withErrors(['vendor' => 'Vendor tidak ditemukan.']);
        }

        return view('vendor.edit', compact('vendor'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'nama_vendor' => ['required', 'string', 'max:100'],
            'badan_hukum' => ['required', 'string', 'max:100'],
        ]);

        DB::statement('UPDATE vendor SET nama_vendor = ?, badan_hukum = ? WHERE idvendor = ?', [
            $validated['nama_vendor'],
            $validated['badan_hukum'],
            $id,
        ]);

        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $usedInPengadaan = DB::selectOne('SELECT COUNT(*) AS total FROM pengadaan WHERE idvendor = ?', [$id]);
        $usedInPenerimaan = DB::selectOne('SELECT COUNT(*) AS total FROM penerimaan WHERE idvendor = ?', [$id]);

        if (($usedInPengadaan->total ?? 0) > 0 || ($usedInPenerimaan->total ?? 0) > 0) {
            DB::statement('UPDATE vendor SET status = 0 WHERE idvendor = ?', [$id]);
            return redirect()->route('vendor.index')->with('success', 'Vendor memiliki riwayat transaksi, status diubah menjadi nonaktif.');
        }

        DB::statement('DELETE FROM vendor WHERE idvendor = ?', [$id]);

        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil dihapus.');
    }

    public function activate(int $id)
    {
        DB::statement('UPDATE vendor SET status = 1 WHERE idvendor = ?', [$id]);

        return redirect()->route('vendor.index')->with('success', 'Vendor berhasil diaktifkan.');
    }
}
