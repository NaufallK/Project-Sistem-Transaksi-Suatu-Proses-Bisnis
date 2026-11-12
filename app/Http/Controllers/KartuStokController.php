<?php

namespace App\Http\Controllers;

use App\Models\KartuStok;

class KartuStokController extends Controller
{
    public function index()
    {
        $kartuStoks = KartuStok::with('barang')
            ->orderBy('idkartu_stok', 'asc')
            ->get();

        return view('kartu-stok.index', compact('kartuStoks'));
    }
}
