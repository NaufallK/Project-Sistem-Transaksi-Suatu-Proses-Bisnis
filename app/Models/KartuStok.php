<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KartuStok extends Model
{
    protected $table = 'kartu_stok';

    protected $primaryKey = 'idkartu_stok';

    public const UPDATED_AT = null;

    protected $fillable = [
        'jenis_transaksi',
        'masuk',
        'keluar',
        'stock',
        'idbarang',
        'idtransaksi',
    ];

    public function barang()
    {
        return $this->belongsTo(
            Barang::class,
            'idbarang',
            'idbarang'
        );
    }
}
