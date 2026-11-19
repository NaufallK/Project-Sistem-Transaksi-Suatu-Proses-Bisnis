<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class KartuStokController extends Controller
{
    public function index()
    {
        $kartuStoks = DB::select('SELECT k.*, b.nama AS barang_nama FROM kartu_stok k LEFT JOIN barang b ON b.idbarang = k.idbarang ORDER BY k.idkartu_stok ASC');
        return view('kartu-stok.index', compact('kartuStoks'));
    }
}
