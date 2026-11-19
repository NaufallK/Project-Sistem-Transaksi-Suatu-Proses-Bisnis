<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengadaanController extends Controller
{
    public function index()
    {
        $pengadaans = DB::select("
            SELECT p.*, u.username, v.nama_vendor 
            FROM pengadaan p 
            JOIN user u ON p.iduser = u.iduser 
            JOIN vendor v ON p.idvendor = v.idvendor 
            ORDER BY p.idpengadaan ASC
        ");

        $details = DB::select("
            SELECT dp.*, b.nama as barang_nama 
            FROM detail_pengadaan dp 
            JOIN barang b ON dp.idbarang = b.idbarang
        ");

        foreach ($pengadaans as $p) {
            $p->user = (object)['username' => $p->username];
            $p->vendor = (object)['nama_vendor' => $p->nama_vendor];
            unset($p->username, $p->nama_vendor);

            $p->details = collect($details)
                ->where('idpengadaan', $p->idpengadaan)
                ->map(function ($d) {
                    $d->barang = (object)['nama' => $d->barang_nama];
                    unset($d->barang_nama);
                    return $d;
                });
        }

        return view('pengadaan.index', compact('pengadaans'));
    }

    public function create()
    {
        return view('pengadaan.create', [
            'users' => DB::select("SELECT iduser, username FROM user ORDER BY username"),
            'vendors' => DB::select("SELECT idvendor, nama_vendor FROM vendor WHERE status = 1 ORDER BY nama_vendor"),
            'barangs' => collect(DB::select("
                SELECT b.idbarang, b.nama, b.harga, s.nama_satuan
                FROM barang b 
                JOIN satuan s ON b.idsatuan = s.idsatuan 
                WHERE b.status = 1 
                ORDER BY b.nama
            "))->map(function ($b) {
                $b->satuan = (object)['nama_satuan' => $b->nama_satuan];
                return $b;
            }),
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

            DB::statement("
                INSERT INTO pengadaan (status, iduser, idvendor, subtotal, ppn, total_nilai, created_at) 
                VALUES ('DRAFT', ?, ?, ?, ?, ?, NOW())
            ", [$validated['iduser'], $validated['idvendor'], $subtotal, $ppn, $subtotal + $ppn]);

            $idpengadaan = DB::selectOne("SELECT LAST_INSERT_ID() as id")->id;

            foreach ($items as $item) {
                $subTotalItem = (float) $item['harga_satuan'] * (int) $item['jumlah'];

                DB::statement("
                    INSERT INTO detail_pengadaan (harga_satuan, jumlah, sub_total, idbarang, idpengadaan) 
                    VALUES (?, ?, ?, ?, ?)
                ", [$item['harga_satuan'], $item['jumlah'], $subTotalItem, $item['idbarang'], $idpengadaan]);
            }
        });

        return redirect()
            ->route('pengadaan.index')
            ->with('success', 'Pengadaan berhasil dibuat dengan status DRAFT.');
    }

    public function destroy($id)
    {
        $exists = DB::selectOne("SELECT idpenerimaan FROM penerimaan WHERE idpengadaan = ?", [$id]);

        if ($exists) {
            return back()->withErrors([
                'pengadaan' => 'Pengadaan yang sudah diterima tidak boleh dihapus.',
            ]);
        }

        DB::transaction(function () use ($id) {
            DB::statement("DELETE FROM detail_pengadaan WHERE idpengadaan = ?", [$id]);
            DB::statement("DELETE FROM pengadaan WHERE idpengadaan = ?", [$id]);
        });

        return redirect()->route('pengadaan.index')
            ->with('success', 'Pengadaan berhasil dihapus.');
    }
}
