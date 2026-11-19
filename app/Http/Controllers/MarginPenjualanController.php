<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarginPenjualanController extends Controller
{
    public function index()
    {
        $margins = DB::select(
            'SELECT m.idmargin_penjualan, m.persen, m.status, m.iduser, m.created_at, m.updated_at, u.username
             FROM margin_penjualan m
             LEFT JOIN user u ON u.iduser = m.iduser
             ORDER BY m.idmargin_penjualan ASC'
        );

        return view('margin.index', compact('margins'));
    }

    public function create()
    {
        $users = DB::select('SELECT iduser, username FROM user ORDER BY username ASC');

        return view('margin.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'persen' => ['required', 'numeric', 'min:0'],
            'iduser' => ['required', 'integer'],
        ]);

        DB::statement(
            'INSERT INTO margin_penjualan (persen, status, iduser) VALUES (?, 1, ?)',
            [(float) $validated['persen'], (int) $validated['iduser']]
        );

        return redirect()->route('margin.index')->with('success', 'Margin penjualan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $margin = DB::selectOne(
            'SELECT idmargin_penjualan, persen, status, iduser FROM margin_penjualan WHERE idmargin_penjualan = ?',
            [$id]
        );

        if (! $margin) {
            return redirect()->route('margin.index')->withErrors(['margin' => 'Margin penjualan tidak ditemukan.']);
        }

        $users = DB::select('SELECT iduser, username FROM user ORDER BY username ASC');

        return view('margin.edit', compact('margin', 'users'));
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'persen' => ['required', 'numeric', 'min:0'],
            'iduser' => ['required', 'integer'],
        ]);

        DB::statement(
            'UPDATE margin_penjualan SET persen = ?, iduser = ? WHERE idmargin_penjualan = ?',
            [(float) $validated['persen'], (int) $validated['iduser'], $id]
        );

        return redirect()->route('margin.index')->with('success', 'Margin penjualan berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $used = DB::selectOne('SELECT COUNT(*) AS total FROM penjualan WHERE idmargin_penjualan = ?', [$id]);
        if (($used->total ?? 0) > 0) {
            DB::statement('UPDATE margin_penjualan SET status = 0 WHERE idmargin_penjualan = ?', [$id]);
            return redirect()->route('margin.index')->with('success', 'Margin pernah digunakan pada transaksi, status diubah menjadi nonaktif.');
        }

        DB::statement('DELETE FROM margin_penjualan WHERE idmargin_penjualan = ?', [$id]);

        return redirect()->route('margin.index')->with('success', 'Margin penjualan berhasil dihapus.');
    }

    public function activate(int $id)
    {
        DB::statement('UPDATE margin_penjualan SET status = 1 WHERE idmargin_penjualan = ?', [$id]);

        return redirect()->route('margin.index')->with('success', 'Margin penjualan berhasil diaktifkan.');
    }
}
