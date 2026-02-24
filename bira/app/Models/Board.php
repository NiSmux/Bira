<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'team_id', 'workflow_group_id'];

    public function items()
    {
        return $this->belongsToMany(
            WorkItem::class,
            'board_items',
            'board_id',
            'item_id'
        );
    }
}