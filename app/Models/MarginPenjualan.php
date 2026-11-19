<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MarginPenjualan extends Model
{
    protected $table = 'margin_penjualan';

    protected $primaryKey = 'idmargin_penjualan';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'persen',
        'status',
        'iduser',
    ];

    protected $casts = [
        'persen' => 'float',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    //public function user()
    {
        return $this->belongsTo(
            User::class,
            'iduser',
            'iduser'
        );
    }
}
