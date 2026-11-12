<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\DetailPenerimaan;
use App\Models\KartuStok;
use App\Models\Penerimaan;
use App\Models\Pengadaan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function index()
    {
        $penerimaans = Penerimaan::with(['user', 'vendor', 'pengadaan', 'details.barang'])
            ->orderBy('idpenerimaan', 'asc')
            ->get();

        return view('penerimaan.index', compact('penerimaans'));
    }

    public function create()
    {
        $pengadaans = Pengadaan::with(['vendor', 'details.barang'])
            ->whereIn('status', ['DRAFT', 'SEBAGIAN'])
            ->orderBy('idpengadaan', 'asc')
            ->get()
            ->map(function ($pengadaan) {
                $items = $pengadaan->details->map(function ($detail) use ($pengadaan) {
                    $sudah = (int) Penerimaan::where('idpengadaan', $pengadaan->idpengadaan)
                        ->join('detail_penerimaan', 'detail_penerimaan.idpenerimaan', '=', 'penerimaan.idpenerimaan')
                        ->where('detail_penerimaan.idbarang', $detail->idbarang)
                        ->sum('detail_penerimaan.jumlah_terima');
                    $sisa = max(0, (int) $detail->jumlah - $sudah);

                    return [
                        'idbarang' => $detail->idbarang,
                        'nama' => $detail->barang->nama ?? '-',
                        'harga' => $detail->harga_satuan,
                        'pesan' => (int) $detail->jumlah,
                        'sudah' => $sudah,
                        'sisa' => $sisa,
                    ];
                })->filter(fn ($item) => $item['sisa'] > 0)->values();

                $pengadaan->items_data = $items;
                $pengadaan->total_sisa = $items->sum('sisa');
                return $pengadaan;
            })
            ->filter(fn ($p) => $p->total_sisa > 0)
            ->values();

        return view('penerimaan.create', [
            'users' => User::orderBy('username')->get(),
            'pengadaans' => $pengadaans,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'iduser' => ['required', 'integer', 'exists:user,iduser'],
            'idpengadaan' => ['required', 'integer', 'exists:pengadaan,idpengadaan'],
            'barang' => ['required', 'array', 'min:1'],
            'barang.*.idbarang' => ['required', 'integer', 'exists:barang,idbarang'],
            'barang.*.jumlah_terima' => ['required', 'integer', 'min:0'],
        ]);

        $pengadaan = Pengadaan::with('details.barang')->findOrFail($validated['idpengadaan']);

        if (! in_array($pengadaan->status, ['DRAFT', 'SEBAGIAN'])) {
            return back()->withErrors([
                'idpengadaan' => 'Pengadaan ini sudah selesai diterima seluruhnya.',
            ]);
        }
        $items = collect($validated['barang'])
            ->filter(fn (array $item) => (int) $item['jumlah_terima'] > 0)
            ->values();

        if ($items->isEmpty()) {
            return back()->withErrors(['barang' => 'Isi jumlah terima minimal satu barang.'])->withInput();
        }

        $detailsById = $pengadaan->details->keyBy('idbarang');

        foreach ($items as $item) {
            $detail = $detailsById->get((int) $item['idbarang']);

            if (! $detail) {
                return back()->withErrors(['barang' => 'Barang tidak termasuk pengadaan terpilih.'])->withInput();
            }

            $jumlahPesan = (int) $detail->jumlah;
            $sudahDiterima = (int) Penerimaan::where('idpengadaan', $pengadaan->idpengadaan)
                ->join('detail_penerimaan', 'detail_penerimaan.idpenerimaan', '=', 'penerimaan.idpenerimaan')
                ->where('detail_penerimaan.idbarang', $detail->idbarang)
                ->sum('detail_penerimaan.jumlah_terima');

            if ((int) $item['jumlah_terima'] > $jumlahPesan - $sudahDiterima) {
                return back()->withErrors([
                    "barang.{$item['idbarang']}.jumlah_terima" => 'Jumlah terima melebihi sisa pesanan.',
                ])->withInput();
            }
        }

        $items = $items->toArray();
        DB::transaction(function () use ($validated, $items, $pengadaan) {
            $subtotal = collect($items)->sum(function (array $item) use ($pengadaan) {
                $detail = $pengadaan->details->firstWhere('idbarang', $item['idbarang']);
                return (float) ($detail->harga_satuan ?? 0) * (int) $item['jumlah_terima'];
            });
            $ppn = round($subtotal * 0.11, 2);
            $penerimaan = Penerimaan::create([
                'status' => 'DITERIMA',
                'iduser' => $validated['iduser'],
                'idvendor' => $pengadaan->idvendor,
                'subtotal' => $subtotal,
                'ppn' => $ppn,
                'total_nilai' => $subtotal + $ppn,
                'idpengadaan' => $pengadaan->idpengadaan,
            ]);

            foreach ($items as $item) {
                $detailPengadaan = $pengadaan->details
                    ->firstWhere('idbarang', $item['idbarang']);
                $subTotalItem = (float) $detailPengadaan->harga_satuan * (int) $item['jumlah_terima'];

                DetailPenerimaan::create([
                    'harga_satuan' => $detailPengadaan->harga_satuan,
                    'jumlah' => $item['jumlah_terima'],
                    'sub_total' => $subTotalItem,
                    'barang_satuan' => $detailPengadaan->barang->satuan->nama_satuan ?? null,
                    'jumlah_terima' => $item['jumlah_terima'],
                    'harga_keluar_terima' => $detailPengadaan->harga_satuan,
                    'sub_total_terima' => $subTotalItem,
                    'idbarang' => $item['idbarang'],
                    'idpenerimaan' => $penerimaan->idpenerimaan,
                ]);
            }
            // Hitung apakah seluruh barang dalam pengadaan sudah lunas diterima
            $totalPesan = (int) $pengadaan->details->sum('jumlah');
            $totalTerima = (int) Penerimaan::where('idpengadaan', $pengadaan->idpengadaan)
                ->join('detail_penerimaan', 'detail_penerimaan.idpenerimaan', '=', 'penerimaan.idpenerimaan')
                ->sum('detail_penerimaan.jumlah_terima');

            $newStatus = ($totalTerima >= $totalPesan) ? 'DITERIMA' : 'SEBAGIAN';
            $pengadaan->update(['status' => $newStatus]);
        });

        return redirect()
            ->route('kartu-stok.index')
            ->with(
                'success',
                'Penerimaan disimpan.'
            );
    }

    public function destroy(Penerimaan $penerimaan)
    {
        DB::transaction(function () use ($penerimaan) {
            KartuStok::where('jenis_transaksi', 'PENERIMAAN')
                ->where('idtransaksi', $penerimaan->idpenerimaan)
                ->delete();
            $penerimaan->details()->delete();

            $pengadaan = $penerimaan->pengadaan;
            $penerimaan->delete();
            if ($pengadaan) {
                $totalTerima = (int) Penerimaan::where('idpengadaan', $pengadaan->idpengadaan)
                    ->join('detail_penerimaan', 'detail_penerimaan.idpenerimaan', '=', 'penerimaan.idpenerimaan')
                    ->sum('detail_penerimaan.jumlah_terima');

                if ($totalTerima === 0) {
                    $pengadaan->update(['status' => 'DRAFT']);
                } else {
                    $totalPesan = (int) $pengadaan->details->sum('jumlah');
                    $pengadaan->update([
                        'status' => ($totalTerima >= $totalPesan) ? 'DITERIMA' : 'SEBAGIAN',
                    ]);
                }
            }
        });

        return redirect()->route('penerimaan.index')
            ->with('success', 'Penerimaan dan kartu stoknya berhasil dihapus.');
    }
}
