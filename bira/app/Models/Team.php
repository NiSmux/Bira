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
        'default_item_type_id',
    ];

    public function defaultItemType()
    {
        return $this->belongsTo(ItemType::class, 'default_item_type_id');
    }

    public function itemTypes()
    {
        return $this->hasMany(ItemType::class, 'team_id');
    }

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