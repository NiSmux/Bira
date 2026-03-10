<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    protected $table = 'priorities';

    public $timestamps = false;

    protected $fillable = ['name', 'order_index'];

    /**
     * Ryšys su užduotimis
     */
    public function workItems()
    {
        return $this->hasMany(WorkItem::class, 'priority_id');
    }
}
