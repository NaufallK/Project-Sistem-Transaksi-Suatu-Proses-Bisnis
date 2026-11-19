<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPengadaan extends Model
{
    protected $table = 'detail_pengadaan';

    protected $primaryKey = 'iddetail_pengadaan';

    public $timestamps = false;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'harga_satuan',
        'jumlah',
        'sub_total',
        'idbarang',
        'idpengadaan',
    ];

    protected $casts = [
        'harga_satuan' => 'float',
        'jumlah' => 'integer',
        'sub_total' => 'float',
    ];

    //public function barang()
    {
        return $this->belongsTo(
            Barang::class,
            'idbarang',
            'idbarang'
        );
    }

    //public function pengadaan()
    {
        return $this->belongsTo(
            Pengadaan::class,
            'idpengadaan',
            'idpengadaan'
        );
    }
}
