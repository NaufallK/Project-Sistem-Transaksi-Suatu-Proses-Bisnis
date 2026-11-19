<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualans = DB::select('SELECT * FROM penjualan ORDER BY idpenjualan ASC');
        foreach ($penjualans as $p) {
            $p->user = DB::selectOne('SELECT * FROM user WHERE iduser = ?', [$p->iduser]);
            $p->margin = DB::selectOne('SELECT * FROM margin_penjualan WHERE idmargin_penjualan = ?', [$p->idmargin_penjualan]);
            $p->details = DB::select('SELECT d.*, b.nama as nama FROM detail_penjualan d JOIN barang b ON d.idbarang = b.idbarang WHERE d.idpenjualan = ?', [$p->idpenjualan]);
            foreach ($p->details as $d) {
                $d->barang = DB::selectOne('SELECT * FROM barang WHERE idbarang = ?', [$d->idbarang]);
            }
        }
        return view('penjualan.index', compact('penjualans'));
    }

    public function create()
    {
        $users = DB::select('SELECT * FROM user ORDER BY username');
        $margins = DB::select('SELECT * FROM margin_penjualan WHERE status = 1 ORDER BY idmargin_penjualan DESC');
        $barangs = DB::select('SELECT * FROM barang WHERE status = 1 ORDER BY nama');
        $stoks = DB::select('SELECT idbarang, SUM(masuk - keluar) as tersedia FROM kartu_stok GROUP BY idbarang');
        $stok = [];
        foreach ($stoks as $s) {
            $stok[$s->idbarang] = (int) $s->tersedia;
        }
        return view('penjualan.create', compact('users', 'margins', 'barangs', 'stok'));
    }

    public function hargaJual(Request $request)
    {
        $data = $request->validate(['idbarang' => ['required', 'integer'], 'idmargin' => ['required', 'integer']]);
        $row = DB::selectOne('SELECT fn_hitung_harga_jual(?, ?) AS harga_jual', [(int) $data['idbarang'], (int) $data['idmargin']]);
        return response()->json(['harga_jual' => (float) ($row->harga_jual ?? 0)]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'iduser' => ['required', 'integer'], 'idmargin_penjualan' => ['required', 'integer'],
            'idbarang' => ['required', 'integer'], 'jumlah' => ['required', 'integer', 'min:1'],
        ]);
        $stokData = DB::selectOne('SELECT SUM(masuk - keluar) as tersedia FROM kartu_stok WHERE idbarang = ?', [$validated['idbarang']]);
        $stokTersedia = (int) ($stokData->tersedia ?? 0);
        if ((int) $validated['jumlah'] > $stokTersedia) {
            return back()->withErrors(['jumlah' => "Stok tidak cukup. Tersedia: {$stokTersedia}."])->withInput();
        }
        $row = DB::selectOne('SELECT fn_hitung_harga_jual(?, ?) AS harga_jual', [(int) $validated['idbarang'], (int) $validated['idmargin_penjualan']]);
        $hargaJual = (float) ($row->harga_jual ?? 0);
        $subtotal = $hargaJual * (int) $validated['jumlah'];
        $ppn = round($subtotal * 0.11, 2);
        DB::transaction(function () use ($validated, $hargaJual, $subtotal, $ppn) {
            DB::statement('INSERT INTO penjualan (subtotal, ppn, total_nilai, iduser, idmargin_penjualan, created_at) VALUES (?, ?, ?, ?, ?, NOW())', [$subtotal, $ppn, $subtotal + $ppn, $validated['iduser'], $validated['idmargin_penjualan']]);
            $idpenjualan = DB::getPdo()->lastInsertId();
            DB::statement('INSERT INTO detail_penjualan (harga_satuan, jumlah, sub_total, idpenjualan, idbarang) VALUES (?, ?, ?, ?, ?)', [$hargaJual, $validated['jumlah'], $subtotal, $idpenjualan, $validated['idbarang']]);
            $stockRow = DB::selectOne('SELECT SUM(masuk - keluar) as total FROM kartu_stok WHERE idbarang = ?', [$validated['idbarang']]);
            $stokTerakhir = (int) ($stockRow->total ?? 0);
            DB::statement('INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idbarang, idtransaksi, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())', ['PENJUALAN', 0, $validated['jumlah'], $stokTerakhir - $validated['jumlah'], $validated['idbarang'], $idpenjualan]);
        });
        return redirect()->route('penjualan.index')->with('success', 'Penjualan disimpan.');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            DB::statement('DELETE FROM kartu_stok WHERE jenis_transaksi = ? AND idtransaksi = ?', ['PENJUALAN', $id]);
            DB::statement('DELETE FROM detail_penjualan WHERE idpenjualan = ?', [$id]);
            DB::statement('DELETE FROM penjualan WHERE idpenjualan = ?', [$id]);
        });
        return redirect()->route('penjualan.index')->with('success', 'Penjualan dan kartu stoknya berhasil dihapus.');
    }
}
