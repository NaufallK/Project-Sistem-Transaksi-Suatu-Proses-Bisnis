<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    protected $primaryKey = 'idpenjualan';

    public const UPDATED_AT = null;

    protected $fillable = [
        'subtotal',
        'ppn',
        'total_nilai',
        'iduser',
        'idmargin_penjualan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'subtotal' => 'float',
        'ppn' => 'float',
        'total_nilai' => 'float',
    ];

    //public function user()
    {
        return $this->belongsTo(User::class, 'iduser', 'iduser');
    }

    //public function margin()
    {
        return $this->belongsTo(
            MarginPenjualan::class,
            'idmargin_penjualan',
            'idmargin_penjualan'
        );
    }

   //public function details()
    {
        return $this->hasMany(
            DetailPenjualan::class,
            'idpenjualan',
            'idpenjualan'
        );
    }
}

