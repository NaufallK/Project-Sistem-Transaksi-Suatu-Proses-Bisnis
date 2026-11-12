<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPengadaan;
use App\Models\Pengadaan;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengadaanController extends Controller
{
    public function index()
    {
        $pengadaans = Pengadaan::with(['user', 'vendor', 'details.barang'])
            ->orderBy('idpengadaan', 'asc')
            ->get();

        return view('pengadaan.index', compact('pengadaans'));
    }

    public function create()
    {
        return view('pengadaan.create', [
            'users' => User::orderBy('username')->get(),
            'vendors' => Vendor::where('status', 1)->orderBy('nama_vendor')->get(),
            'barangs' => Barang::with('satuan')
                ->where('status', 1)
                ->orderBy('nama')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'iduser' => ['required', 'integer', 'exists:user,iduser'],
            'idvendor' => ['required', 'integer', 'exists:vendor,idvendor'],
            'barang' => ['required', 'array', 'min:1'],
            'barang.*.idbarang' => ['required', 'integer', 'exists:barang,idbarang'],
            'barang.*.harga_satuan' => ['required', 'numeric', 'min:0'],
            'barang.*.jumlah' => ['required', 'integer', 'min:0'],
        ]);

        $items = collect($validated['barang'])
            ->filter(fn (array $item) => (int) $item['jumlah'] > 0)
            ->values()
            ->all();

        if (count($items) === 0) {
            return back()
                ->withErrors(['barang' => 'Isi jumlah minimal satu barang.'])
                ->withInput();
        }

        DB::transaction(function () use ($validated, $items) {
            $subtotal = 0.0;

            foreach ($items as $item) {
                $subtotal += (float) $item['harga_satuan'] * (int) $item['jumlah'];
            }

            $ppn = round($subtotal * 0.11, 2);

            $pengadaan = Pengadaan::create([
                'status' => 'DRAFT',
                'iduser' => $validated['iduser'],
                'idvendor' => $validated['idvendor'],
                'subtotal' => $subtotal,
                'ppn' => $ppn,
                'total_nilai' => $subtotal + $ppn,
            ]);

            foreach ($items as $item) {
                $subTotalItem = (float) $item['harga_satuan'] * (int) $item['jumlah'];

                DetailPengadaan::create([
                    'harga_satuan' => $item['harga_satuan'],
                    'jumlah' => $item['jumlah'],
                    'sub_total' => $subTotalItem,
                    'idbarang' => $item['idbarang'],
                    'idpengadaan' => $pengadaan->idpengadaan,
                ]);
            }
        });

        return redirect()
            ->route('pengadaan.index')
            ->with('success', 'Pengadaan berhasil dibuat dengan status DRAFT.');
    }

    public function destroy(Pengadaan $pengadaan)
    {
        if ($pengadaan->penerimaans()->exists()) {
            return back()->withErrors([
                'pengadaan' => 'Pengadaan yang sudah diterima tidak boleh dihapus.',
            ]);
        }

        DB::transaction(function () use ($pengadaan) {
            $pengadaan->details()->delete();
            $pengadaan->delete();
        });

        return redirect()->route('pengadaan.index')
            ->with('success', 'Pengadaan berhasil dihapus.');
    }
}
