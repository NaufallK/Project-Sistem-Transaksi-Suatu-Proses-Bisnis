<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::with('satuan')
            ->orderBy('idbarang', 'asc')
            ->get();

        return view('barang.index', compact('barangs'));
    }

    public function create()
    {
        $satuans = Satuan::where('status', 1)
            ->orderBy('nama_satuan')
            ->get();

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

        DB::statement('CALL sp_tambah_barang(?, ?, ?, ?)', [
            $validated['nama'],
            $validated['jenis'],
            (int) $validated['idsatuan'],
            (float) $validated['harga'],
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function destroy(Barang $barang)
    {
        $barang->update([
            'status' => 0,
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil dinonaktifkan.');
    }

    public function activate(Barang $barang)
    {
        $barang->update([
            'status' => 1,
        ]);

        return redirect()
            ->route('barang.index')
            ->with('success', 'Barang berhasil diaktifkan.');
    }
}
