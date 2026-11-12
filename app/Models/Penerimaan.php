<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penerimaan extends Model
{
    protected $table = 'penerimaan';

    protected $primaryKey = 'idpenerimaan';

    public const UPDATED_AT = null;

    protected $fillable = [
        'status',
        'iduser',
        'idvendor',
        'subtotal',
        'ppn',
        'total_nilai',
        'idpengadaan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'subtotal' => 'float',
        'ppn' => 'float',
        'total_nilai' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'iduser', 'iduser');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'idvendor', 'idvendor');
    }

    public function pengadaan()
    {
        return $this->belongsTo(Pengadaan::class, 'idpengadaan', 'idpengadaan');
    }

    public function details()
    {
        return $this->hasMany(
            DetailPenerimaan::class,
            'idpenerimaan',
            'idpenerimaan'
        );
    }

}
