<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'team_id', 'workflow_group_id', 'estimation_mode'];

    public function items()
    {
        return $this->belongsToMany(
            WorkItem::class,
            'board_items',
            'board_id',
            'item_id'
        );
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'board_members', 'board_id', 'user_id')
            ->withPivot('role', 'assigned_at');
    }

    public function sprints()
    {
        return $this->hasMany(Sprint::class);
    }

    public function subTeams()
    {
        return $this->hasMany(BoardSubTeam::class, 'board_id');
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }
}