<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenerimaan extends Model
{
    protected $table = 'detail_penerimaan';

    protected $primaryKey = 'iddetail_penerimaan';

    public $timestamps = false;

    protected $fillable = [
        'harga_satuan',
        'jumlah',
        'sub_total',
        'barang_satuan',
        'jumlah_terima',
        'harga_keluar_terima',
        'sub_total_terima',
        'idbarang',
        'idpenerimaan',
    ];

    protected $casts = [
        'harga_satuan' => 'float',
        'jumlah' => 'integer',
        'sub_total' => 'float',
        'jumlah_terima' => 'integer',
        'harga_keluar_terima' => 'float',
        'sub_total_terima' => 'float',
    ];

    //public function barang()
    {
        return $this->belongsTo(Barang::class, 'idbarang', 'idbarang');
    }

    //public function penerimaan()
    {
        return $this->belongsTo(Penerimaan::class, 'idpenerimaan', 'idpenerimaan');
    }
}
