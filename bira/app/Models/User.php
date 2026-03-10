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
    public function getAuthIdentifierName()
    {
        return 'email'; // Jau OK pagal nutylėjimą
    }   

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')
            ->withPivot('role_in_team', 'joined_at');
    }

    // Tavo DB turi tik created_at, todėl išjungiame automatinį updated_at valdymą
    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    public $timestamps = true;
}