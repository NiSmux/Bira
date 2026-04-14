<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkItemComment extends Model
{
    protected $fillable = ['work_item_id', 'user_id', 'body'];

    public function workItem()
    {
        return $this->belongsTo(WorkItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
