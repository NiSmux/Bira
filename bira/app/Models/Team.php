<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'teams';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_members', 'team_id', 'user_id')
            ->withPivot('role_in_team', 'joined_at');
    }

    public function boards()
    {
        return $this->hasMany(Board::class, 'team_id');
    }
}