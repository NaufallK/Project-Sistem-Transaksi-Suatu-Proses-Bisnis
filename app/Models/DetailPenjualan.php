<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    protected $table = 'detail_penjualan';

    protected $primaryKey = 'iddetail_penjualan';

    public $timestamps = false;

    protected $fillable = [
        'harga_satuan',
        'jumlah',
        'sub_total',
        'idpenjualan',
        'idbarang',
    ];

    protected $casts = [
        'harga_satuan' => 'float',
        'jumlah' => 'integer',
        'sub_total' => 'float',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'idbarang', 'idbarang');
    }

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'idpenjualan', 'idpenjualan');
    }
}
