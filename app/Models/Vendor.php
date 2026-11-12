<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendor';

    protected $primaryKey = 'idvendor';

    public $timestamps = false;

    protected $fillable = [
        'nama_vendor',
        'badan_hukum',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function pengadaan()
    {
        return $this->hasMany(
            Pengadaan::class,
            'idvendor',
            'idvendor'
        );
    }
}
