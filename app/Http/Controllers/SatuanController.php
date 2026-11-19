<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SatuanController extends Controller
{
    public function index()
    {
        $satuans = DB::select('SELECT idsatuan, nama_satuan, status FROM satuan ORDER BY idsatuan ASC');

        return view('satuan.index', compact('satuans'));
    }

    public function create()
    {
        return view('satuan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satuan' => ['required', 'string', 'max:50'],
        ]);

        DB::statement('INSERT INTO satuan (nama_satuan, status) VALUES (?, 1)', [
            $validated['nama_satuan'],
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $satuan = DB::selectOne('SELECT idsatuan, nama_satuan, status FROM satuan WHERE idsatuan = ?', [$id]);

        if (! $satuan) {
            return redirect()->route('satuan.index')->withErrors(['satuan' => 'Satuan tidak ditemukan.']);
        }

        return view('satuan.edit', compact('satuan'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'nama_satuan' => ['required', 'string', 'max:50'],
        ]);

        DB::statement('UPDATE satuan SET nama_satuan = ? WHERE idsatuan = ?', [
            $validated['nama_satuan'],
            $id,
        ]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        // Check apakah dipakai di barang
        $used = DB::selectOne('SELECT COUNT(*) AS total FROM barang WHERE idsatuan = ?', [$id]);
        if (($used->total ?? 0) > 0) {
            // Soft delete/nonaktifkan jika masih ada relasi
            DB::statement('UPDATE satuan SET status = 0 WHERE idsatuan = ?', [$id]);
            return redirect()->route('satuan.index')->with('success', 'Satuan sedang dipakai, status diubah menjadi nonaktif.');
        }

        DB::statement('DELETE FROM satuan WHERE idsatuan = ?', [$id]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil dihapus.');
    }

    public function activate(int $id)
    {
        DB::statement('UPDATE satuan SET status = 1 WHERE idsatuan = ?', [$id]);

        return redirect()->route('satuan.index')->with('success', 'Satuan berhasil diaktifkan.');
    }
}
