<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function index()
    {
        $penerimaans = DB::select('
            SELECT pe.*, u.username, v.nama_vendor
            FROM penerimaan pe
            JOIN `user` u ON u.iduser = pe.iduser
            JOIN vendor v ON v.idvendor = pe.idvendor
            ORDER BY pe.idpenerimaan ASC
        ');

        $details = DB::select('
            SELECT dp.idpenerimaan, dp.idbarang, dp.jumlah_terima, b.nama AS barang_nama
            FROM detail_penerimaan dp
            JOIN barang b ON b.idbarang = dp.idbarang
        ');

        foreach ($penerimaans as $penerimaan) {
            $penerimaan->user = (object) ['username' => $penerimaan->username];
            $penerimaan->vendor = (object) ['nama_vendor' => $penerimaan->nama_vendor];

            $penerimaan->details = array_values(array_filter(
                array_map(function ($detail) {
                    $detail->barang = (object) ['nama' => $detail->barang_nama];

                    return $detail;
                }, $details),
                fn ($detail) => (int) $detail->idpenerimaan === (int) $penerimaan->idpenerimaan
            ));
        }

        return view('penerimaan.index', compact('penerimaans'));
    }

    public function create()
    {
        $users = DB::select('SELECT iduser, username FROM `user` ORDER BY username ASC');

        // Sisa pesanan per barang dihitung langsung di MySQL dengan LEFT JOIN agregat.
        $rows = DB::select('
            SELECT
                p.idpengadaan,
                p.status,
                v.nama_vendor,
                dp.idbarang,
                b.nama AS nama_barang,
                dp.harga_satuan,
                dp.jumlah AS pesan,
                COALESCE((
                    SELECT SUM(dpe.jumlah_terima)
                    FROM detail_penerimaan dpe
                    JOIN penerimaan pe2 ON pe2.idpenerimaan = dpe.idpenerimaan
                    WHERE pe2.idpengadaan = p.idpengadaan
                      AND dpe.idbarang = dp.idbarang
                ), 0) AS sudah
            FROM pengadaan p
            JOIN vendor v ON v.idvendor = p.idvendor
            JOIN detail_pengadaan dp ON dp.idpengadaan = p.idpengadaan
            JOIN barang b ON b.idbarang = dp.idbarang
            WHERE p.status IN (\'DRAFT\', \'SEBAGIAN\')
            ORDER BY p.idpengadaan ASC, b.nama ASC
        ');

        $pengadaans = [];
        foreach ($rows as $row) {
            $sisa = max(0, (int) $row->pesan - (int) $row->sudah);
            if ($sisa === 0) {
                continue;
            }

            $id = (int) $row->idpengadaan;
            if (! isset($pengadaans[$id])) {
                $pengadaans[$id] = (object) [
                    'idpengadaan' => $id,
                    'status' => $row->status,
                    'vendor' => (object) ['nama_vendor' => $row->nama_vendor],
                    'items_data' => [],
                ];
            }

            $pengadaans[$id]->items_data[] = [
                'idbarang' => (int) $row->idbarang,
                'nama' => $row->nama_barang,
                'harga' => (float) $row->harga_satuan,
                'pesan' => (int) $row->pesan,
                'sudah' => (int) $row->sudah,
                'sisa' => $sisa,
            ];
        }

        foreach ($pengadaans as $pengadaan) {
            $pengadaan->items_data_json = json_encode($pengadaan->items_data);
        }

        return view('penerimaan.create', [
            'users' => $users,
            'pengadaans' => array_values($pengadaans),
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

        $pengadaan = DB::selectOne(
            'SELECT idpengadaan, status, idvendor FROM pengadaan WHERE idpengadaan = ?',
            [(int) $validated['idpengadaan']]
        );

        if (! $pengadaan) {
            return back()->withErrors(['idpengadaan' => 'Pengadaan tidak ditemukan.'])->withInput();
        }

        if (! in_array($pengadaan->status, ['DRAFT', 'SEBAGIAN'], true)) {
            return back()->withErrors([
                'idpengadaan' => 'Pengadaan ini sudah selesai diterima seluruhnya.',
            ]);
        }

        $items = array_values(array_filter(
            $validated['barang'],
            fn (array $item) => (int) $item['jumlah_terima'] > 0
        ));

        if (count($items) === 0) {
            return back()->withErrors(['barang' => 'Isi jumlah terima minimal satu barang.'])->withInput();
        }

        $detailPesanan = DB::select('
            SELECT dp.idbarang, dp.harga_satuan, dp.jumlah, b.nama AS nama_barang, s.nama_satuan
            FROM detail_pengadaan dp
            JOIN barang b ON b.idbarang = dp.idbarang
            LEFT JOIN satuan s ON s.idsatuan = b.idsatuan
            WHERE dp.idpengadaan = ?
        ', [(int) $pengadaan->idpengadaan]);

        $pesananById = [];
        foreach ($detailPesanan as $detail) {
            $pesananById[(int) $detail->idbarang] = $detail;
        }

        foreach ($items as $item) {
            $idbarang = (int) $item['idbarang'];

            if (! isset($pesananById[$idbarang])) {
                return back()->withErrors(['barang' => 'Barang tidak termasuk pengadaan terpilih.'])->withInput();
            }

            $sudah = DB::selectOne('
                SELECT COALESCE(SUM(dpe.jumlah_terima), 0) AS total
                FROM detail_penerimaan dpe
                JOIN penerimaan pe ON pe.idpenerimaan = dpe.idpenerimaan
                WHERE pe.idpengadaan = ? AND dpe.idbarang = ?
            ', [(int) $pengadaan->idpengadaan, $idbarang]);

            $sisa = (int) $pesananById[$idbarang]->jumlah - (int) $sudah->total;

            if ((int) $item['jumlah_terima'] > $sisa) {
                return back()->withErrors([
                    "barang.{$idbarang}.jumlah_terima" => 'Jumlah terima melebihi sisa pesanan.',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $items, $pengadaan, $pesananById) {
            $subtotal = 0.0;
            foreach ($items as $item) {
                $subtotal += (float) $pesananById[(int) $item['idbarang']]->harga_satuan
                    * (int) $item['jumlah_terima'];
            }

            $ppn = round($subtotal * 0.11, 2);

            DB::statement('
                INSERT INTO penerimaan (status, iduser, idvendor, subtotal, ppn, total_nilai, idpengadaan, created_at)
                VALUES (\'DITERIMA\', ?, ?, ?, ?, ?, ?, NOW())
            ', [
                (int) $validated['iduser'],
                (int) $pengadaan->idvendor,
                $subtotal,
                $ppn,
                $subtotal + $ppn,
                (int) $pengadaan->idpengadaan,
            ]);

            $idpenerimaan = (int) DB::selectOne('SELECT LAST_INSERT_ID() AS id')->id;

            foreach ($items as $item) {
                $detail = $pesananById[(int) $item['idbarang']];
                $subTotalItem = (float) $detail->harga_satuan * (int) $item['jumlah_terima'];

                // INSERT ini memicu TRIGGER trg_kartu_stok yang mencatat stok masuk.
                DB::statement('
                    INSERT INTO detail_penerimaan
                        (harga_satuan, jumlah, sub_total, barang_satuan, jumlah_terima,
                         harga_keluar_terima, sub_total_terima, idbarang, idpenerimaan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ', [
                    (float) $detail->harga_satuan,
                    (int) $item['jumlah_terima'],
                    $subTotalItem,
                    $detail->nama_satuan,
                    (int) $item['jumlah_terima'],
                    (float) $detail->harga_satuan,
                    $subTotalItem,
                    (int) $item['idbarang'],
                    $idpenerimaan,
                ]);
            }

            $totals = DB::selectOne('
                SELECT
                    (SELECT COALESCE(SUM(jumlah), 0) FROM detail_pengadaan WHERE idpengadaan = ?) AS total_pesan,
                    (
                        SELECT COALESCE(SUM(dpe.jumlah_terima), 0)
                        FROM detail_penerimaan dpe
                        JOIN penerimaan pe2 ON pe2.idpenerimaan = dpe.idpenerimaan
                        WHERE pe2.idpengadaan = ?
                    ) AS total_terima
            ', [(int) $pengadaan->idpengadaan, (int) $pengadaan->idpengadaan]);

            $newStatus = ((int) $totals->total_terima >= (int) $totals->total_pesan) ? 'DITERIMA' : 'SEBAGIAN';

            DB::statement('UPDATE pengadaan SET status = ? WHERE idpengadaan = ?', [
                $newStatus,
                (int) $pengadaan->idpengadaan,
            ]);
        });

        return redirect()
            ->route('kartu-stok.index')
            ->with('success', 'Penerimaan disimpan. Kartu stok sudah tercatat ya sayang');
    }

    public function destroy(int $id)
    {
        DB::transaction(function () use ($id) {
            $penerimaan = DB::selectOne(
                'SELECT idpenerimaan, idpengadaan FROM penerimaan WHERE idpenerimaan = ?',
                [$id]
            );

            if (! $penerimaan) {
                return;
            }

            // Hapus dulu kartu_stok yang dibuat trigger, agar tidak ada mutasi yatim.
            DB::statement(
                'DELETE FROM kartu_stok WHERE jenis_transaksi = ? AND idtransaksi = ?',
                ['PENERIMAAN', $id]
            );

            DB::statement('DELETE FROM detail_penerimaan WHERE idpenerimaan = ?', [$id]);
            DB::statement('DELETE FROM penerimaan WHERE idpenerimaan = ?', [$id]);

            $totals = DB::selectOne('
                SELECT
                    (SELECT COALESCE(SUM(jumlah), 0) FROM detail_pengadaan WHERE idpengadaan = ?) AS total_pesan,
                    (
                        SELECT COALESCE(SUM(dpe.jumlah_terima), 0)
                        FROM detail_penerimaan dpe
                        JOIN penerimaan pe2 ON pe2.idpenerimaan = dpe.idpenerimaan
                        WHERE pe2.idpengadaan = ?
                    ) AS total_terima
            ', [(int) $penerimaan->idpengadaan, (int) $penerimaan->idpengadaan]);

            $newStatus = match (true) {
                (int) $totals->total_terima === 0 => 'DRAFT',
                (int) $totals->total_terima >= (int) $totals->total_pesan => 'DITERIMA',
                default => 'SEBAGIAN',
            };

            DB::statement('UPDATE pengadaan SET status = ? WHERE idpengadaan = ?', [
                $newStatus,
                (int) $penerimaan->idpengadaan,
            ]);
        });

        return redirect()->route('penerimaan.index')
            ->with('success', 'Penerimaan dan kartu stoknya berhasil dihapus.');
    }
}
