<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $table = 'user';

    protected $primaryKey = 'iduser';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'password',
        'idrole',
    ];

    protected $hidden = [
        'password',
    ];

    public function role()
    {
        return $this->belongsTo(
            Role::class,
            'idrole',
            'idrole'
        );
    }

    public function getAuthPassword()
    {
        return $this->password;
    }
}
