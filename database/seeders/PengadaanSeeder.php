<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pengadaan;

class PengadaanSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan user 1 dan vendor 1 ada
        Pengadaan::create([
            'status'      => 'DRAFT',
            'iduser'      => 1,
            'idvendor'    => 1,
            'subtotal'    => 0,
            'ppn'         => 0,
            'total_nilai' => 0,
        ]);
    }
}
