<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';

    protected $primaryKey = 'idrole';

    public $timestamps = false;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'nama_role',
    ];

    //public function users()
    {
        return $this->hasMany(
            User::class,
            'idrole',
            'idrole'
        );
    }
}
