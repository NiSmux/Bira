<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users'; // Tavo lentelė

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
    ];

    // PASAKOME LARAVEL, KAD SLAPTAŽODIS YRA ŠIAME STULPELYJE
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Tavo DB turi tik created_at, todėl išjungiame automatinį updated_at valdymą
    public $timestamps = false; 
}