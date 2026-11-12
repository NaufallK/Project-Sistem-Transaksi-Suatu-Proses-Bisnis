<?php

namespace App\Models;

use App\Models\DetailPengadaan;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    protected $table = 'pengadaan';

    protected $primaryKey = 'idpengadaan';

    public $incrementing = true;

    protected $keyType = 'int';

    public const UPDATED_AT = null;

    protected $fillable = [
        'status',
        'iduser',
        'idvendor',
        'subtotal',
        'ppn',
        'total_nilai',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'subtotal' => 'float',
        'ppn' => 'float',
        'total_nilai' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'iduser',
            'iduser'
        );
    }

    public function vendor()
    {
        return $this->belongsTo(
            Vendor::class,
            'idvendor',
            'idvendor'
        );
    }

    public function details()
    {
        return $this->hasMany(
            DetailPengadaan::class,
            'idpengadaan',
            'idpengadaan'
        );
    }

    public function penerimaans()
    {
        return $this->hasMany(
            Penerimaan::class,
            'idpengadaan',
            'idpengadaan'
        );
    }
}
