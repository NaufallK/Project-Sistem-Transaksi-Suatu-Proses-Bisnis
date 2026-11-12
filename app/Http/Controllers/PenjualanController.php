<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPenjualan;
use App\Models\KartuStok;
use App\Models\MarginPenjualan;
use App\Models\Penjualan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenjualanController extends Controller
{
    public function index()
    {
        $penjualans = Penjualan::with(['user', 'margin', 'details.barang'])
            ->orderBy('idpenjualan', 'asc')
            ->get();

        return view('penjualan.index', compact('penjualans'));
    }

    public function create()
    {
        return view('penjualan.create', [
            'users' => User::orderBy('username')->get(),
            'margins' => MarginPenjualan::where('status', 1)
                ->orderByDesc('idmargin_penjualan')
                ->get(),
            'barangs' => Barang::where('status', 1)->orderBy('nama')->get(),
            'stok' => KartuStok::query()
                ->select('idbarang', DB::raw('SUM(masuk - keluar) AS tersedia'))
                ->groupBy('idbarang')
                ->pluck('tersedia', 'idbarang'),
        ]);
    }

    /**
     * Endpoint bantu untuk preview harga jual.
     * Nilainya diambil langsung dari function database fn_hitung_harga_jual().
     */
    public function hargaJual(Request $request)
    {
        $data = $request->validate([
            'idbarang' => ['required', 'integer', 'exists:barang,idbarang'],
            'idmargin' => ['required', 'integer', 'exists:margin_penjualan,idmargin_penjualan'],
        ]);

        $row = DB::selectOne(
            'SELECT fn_hitung_harga_jual(?, ?) AS harga_jual',
            [(int) $data['idbarang'], (int) $data['idmargin']]
        );

        return response()->json([
            'harga_jual' => (float) $row->harga_jual,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'iduser' => ['required', 'integer', 'exists:user,iduser'],
            'idmargin_penjualan' => ['required', 'integer', 'exists:margin_penjualan,idmargin_penjualan'],
            'idbarang' => ['required', 'integer', 'exists:barang,idbarang'],
            'jumlah' => ['required', 'integer', 'min:1'],
        ]);
        $stokTersedia = (int) KartuStok::query()
            ->where('idbarang', $validated['idbarang'])
            ->sum(DB::raw('masuk - keluar'));

        if ((int) $validated['jumlah'] > $stokTersedia) {
            return back()
                ->withErrors([
                    'jumlah' => "Stok tidak cukup. Tersedia: {$stokTersedia}.",
                ])
                ->withInput();
        }

        // Harga jual dihitung oleh FUNCTION database, bukan oleh PHP.
        $row = DB::selectOne(
            'SELECT fn_hitung_harga_jual(?, ?) AS harga_jual',
            [(int) $validated['idbarang'], (int) $validated['idmargin_penjualan']]
        );

        $hargaJual = (float) $row->harga_jual;
        $subtotal = $hargaJual * (int) $validated['jumlah'];
        $ppn = round($subtotal * 0.11, 2);

        DB::transaction(function () use ($validated, $hargaJual, $subtotal, $ppn) {
            $penjualan = Penjualan::create([
                'subtotal' => $subtotal,
                'ppn' => $ppn,
                'total_nilai' => $subtotal + $ppn,
                'iduser' => $validated['iduser'],
                'idmargin_penjualan' => $validated['idmargin_penjualan'],
            ]);

            DetailPenjualan::create([
                'harga_satuan' => $hargaJual,
                'jumlah' => $validated['jumlah'],
                'sub_total' => $subtotal,
                'idpenjualan' => $penjualan->idpenjualan,
                'idbarang' => $validated['idbarang'],
            ]);

            $stokTerakhir = (int) KartuStok::where(
                'idbarang',
                $validated['idbarang']
            )->sum(DB::raw('masuk - keluar'));

            KartuStok::create([
                'jenis_transaksi' => 'PENJUALAN',
                'masuk' => 0,
                'keluar' => $validated['jumlah'],
                'stock' => $stokTerakhir - $validated['jumlah'],
                'idbarang' => $validated['idbarang'],
                'idtransaksi' => $penjualan->idpenjualan,
            ]);
        });

        return redirect()
            ->route('penjualan.index')
            ->with(
                'success',
                'Penjualan disimpan.'
            );
    }

    public function destroy(Penjualan $penjualan)
    {
        DB::transaction(function () use ($penjualan) {
            KartuStok::where('jenis_transaksi', 'PENJUALAN')
                ->where('idtransaksi', $penjualan->idpenjualan)
                ->delete();
            $penjualan->details()->delete();
            $penjualan->delete();
        });

        return redirect()->route('penjualan.index')
            ->with('success', 'Penjualan dan kartu stoknya berhasil dihapus.');
    }
}
