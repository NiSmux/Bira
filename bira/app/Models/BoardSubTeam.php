<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardSubTeam extends Model
{
    protected $table = 'board_sub_teams';

    public $timestamps = false;

    protected $fillable = [
        'board_id',
        'name',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function members()
    {
        return $this->belongsToMany(
            User::class,
            'board_sub_team_members',
            'sub_team_id',
            'user_id'
        );
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workItems()
    {
        return $this->hasMany(WorkItem::class, 'sub_team_id');
    }
}
