<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['board_id', 'name', 'color', 'is_custom'];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function workItems()
    {
        return $this->belongsToMany(WorkItem::class, 'work_item_tags', 'tag_id', 'work_item_id');
    }
}
