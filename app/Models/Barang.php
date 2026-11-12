<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $primaryKey = 'idbarang';

    public $timestamps = false;

    protected $fillable = [
        'jenis',
        'nama',
        'idsatuan',
        'status',
        'harga',
    ];

    protected $casts = [
        'status' => 'boolean',
        'harga' => 'float',
    ];

    public function satuan()
    {
        return $this->belongsTo(
            Satuan::class,
            'idsatuan',
            'idsatuan'
        );
    }

    public function detailPengadaan()
    {
        return $this->hasMany(
            DetailPengadaan::class,
            'idbarang',
            'idbarang'
        );
    }

    public function detailPenjualan()
    {
        return $this->hasMany(
            DetailPenjualan::class,
            'idbarang',
            'idbarang'
        );
    }

    public function kartuStok()
    {
        return $this->hasMany(
            KartuStok::class,
            'idbarang',
            'idbarang'
        );
    }
}
