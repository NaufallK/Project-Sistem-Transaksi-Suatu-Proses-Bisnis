<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MarginPenjualan;

class MarginPenjualanSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan user dengan id 1 ada (dari SQL)
        MarginPenjualan::create([
            'persen' => 10,          // 10 %
            'status' => 1,           // aktif
            'iduser' => 1,           // user admin
        ]);
    }
}
